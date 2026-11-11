<?php

namespace App\Livewire\Expenses;

use Livewire\Component;
use App\Models\Expense;
use App\Models\Category;
use App\Services\AI\CategoryMatcher;
use App\Services\AI\ExpenseCategorizer;
use App\Services\AI\ReceiptParser;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Index extends Component
{

    // Expense header (one purchase / receipt)

    public $description = '';   // title of the whole purchase, e.g. "SM Supermarket"
    public $expense_date = '';
    public $notes = '';


    // Items on the expense. Each row:
    // ['uid', 'description', 'category_id', 'amount',
    //  'category_source' ('manual'|'history'|'ai'), 'ai_suggested_category_id', 'ai_confidence']

    public array $items = [];


    // Categories the user can choose from, for the category dropdowns: [['id' => 1, 'name' => 'Food'], ...]
    // (display only: every chosen category is validated again on save)

    public array $categoryOptions = [];


    // Receipt scanning

    public ?float $printedTotal = null;   // total printed on the scanned receipt (to compare with the items' sum)
    public ?string $scanMessage = null;   // feedback shown after a scan


    // IDs

    public $editingExpenseId = null;
    public $deleteExpenseId = null;


    // Modal States

    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showUnsavedModal = false;
    public $showEditUnsavedModal = false;

    public function mount()
    {
        $this->expense_date = now()->toDateString();
        $this->items = [$this->blankItem()];
        $this->refreshCategoryOptions();
    }

    protected function rules()
    {
        return [
            'description' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',

            'items' => 'required|array|min:1|max:50',
            'items.*.description' => 'required|string|max:255',
            'items.*.category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query->where(
                        fn ($q) => $q->whereNull('user_id')->orWhere('user_id', Auth::id())
                    )
                ),
            ],
            'items.*.amount' => 'required|numeric|min:1',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'items.*.description' => 'item name',
            'items.*.category_id' => 'category',
            'items.*.amount' => 'amount',
        ];
    }


    //  ITEMS


    protected function blankItem(): array
    {
        return [
            'uid' => (string) Str::uuid(),
            'description' => '',
            'category_id' => '',
            'amount' => '',
            'category_source' => 'manual',
            'ai_suggested_category_id' => null,
            'ai_confidence' => null,
        ];
    }

    public function addItem(): void
    {
        if (count($this->items) < 50) {
            $this->items[] = $this->blankItem();
        }
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) {
            return; // an expense always keeps at least one item
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    protected function itemsTotal(): float
    {
        return round(
            collect($this->items)->sum(
                fn ($item) => is_numeric($item['amount'] ?? null) ? (float) $item['amount'] : 0
            ),
            2
        );
    }

    /** Only the columns that belong in the expense_items table. */
    protected function itemRows(): array
    {
        return collect($this->items)->map(fn ($item) => [
            'description' => trim($item['description']),
            'category_id' => $item['category_id'],
            'amount' => $item['amount'],
            'category_source' => $item['category_source'] ?? 'manual',
            'ai_suggested_category_id' => $item['ai_suggested_category_id'] ?? null,
            'ai_confidence' => $item['ai_confidence'] ?? null,
        ])->all();
    }


    //  AI CATEGORY SUGGESTION (per item)


    public function updatedItems($value, $key): void
    {
        [$index, $field] = array_pad(explode('.', $key, 2), 2, null);

        if ($field === null || ! isset($this->items[$index])) {
            return;
        }

        // The user picked a category themselves: it is a manual choice from now on.
        if ($field === 'category_id') {
            $this->items[$index]['category_source'] = 'manual';
            return;
        }

        if ($field === 'description') {
            $this->suggestCategoryFor((int) $index);
        }
    }

    protected function suggestCategoryFor(int $index): void
    {
        // Only suggest on the "add expense" form, never while editing an existing expense.
        if ($this->showEditModal) {
            return;
        }

        $item = $this->items[$index];

        // Never override a category the user picked themselves.
        if (filled($item['category_id']) && $item['category_source'] === 'manual') {
            return;
        }

        $suggestion = app(ExpenseCategorizer::class)->suggest(
            Auth::id(),
            (string) $item['description'],
            $this->allCategories(),
        );

        if ($suggestion) {
            $this->items[$index]['category_id'] = $suggestion['category_id'];
            $this->items[$index]['category_source'] = $suggestion['source'];
            $this->items[$index]['ai_suggested_category_id'] = $suggestion['source'] === 'ai' ? $suggestion['category_id'] : null;
            $this->items[$index]['ai_confidence'] = $suggestion['source'] === 'ai' ? $suggestion['confidence'] : null;
        } elseif ($item['category_source'] !== 'manual') {
            // The old suggestion no longer applies to the new item name.
            $this->items[$index]['category_id'] = '';
            $this->items[$index]['category_source'] = 'manual';
            $this->items[$index]['ai_suggested_category_id'] = null;
            $this->items[$index]['ai_confidence'] = null;
        }
    }

    /** The shared categories plus the ones this user created. */
    protected function allCategories()
    {
        return Category::visibleTo(Auth::id())->orderBy('category_name')->get();
    }


    //  CATEGORY DROPDOWN (choose an existing category or add a new one)


    protected function refreshCategoryOptions(): void
    {
        $this->categoryOptions = $this->allCategories()
            ->map(fn ($category) => ['id' => $category->id, 'name' => $category->category_name])
            ->values()
            ->all();
    }

    protected function selectCategory(int $index, Category $category): void
    {
        $this->items[$index]['category_id'] = $category->id;
        $this->items[$index]['category_source'] = 'manual';

        $this->refreshCategoryOptions();
    }

    protected function cleanCategoryName(string $name): ?string
    {
        $name = Str::of($name)->squish()->toString();

        if (mb_strlen($name) < 2 || mb_strlen($name) > 40 || ! preg_match('/^[\p{L}\p{N} &\'\-\.\/]+$/u', $name)) {
            return null;
        }

        return mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1);
    }

    /**
     * Called from the category dropdown when the user types a name that isn't in the list.
     *
     * status 'exists'  -> it was already there; it is now selected for the item
     * status 'suggest' -> it looks like an existing category: the dropdown asks "Did you mean ...?"
     * status 'created' -> a new category was saved (only for this user) and selected for the item
     * status 'invalid' -> message explains what is wrong
     *
     * $force = true skips the "did you mean" check (the user said "No, add mine").
     */
    public function addCategory(string $uid, string $name, bool $force = false): array
    {
        $index = collect($this->items)->search(fn ($item) => ($item['uid'] ?? null) === $uid);

        if ($index === false) {
            return ['status' => 'invalid', 'message' => 'That item no longer exists.'];
        }

        $name = $this->cleanCategoryName($name);

        if ($name === null) {
            return ['status' => 'invalid', 'message' => 'Use 2 to 40 characters: letters, numbers, spaces, & and -.'];
        }

        $categories = $this->allCategories();

        // 1) Already there (ignoring capital letters)? Just use it.
        $existing = $categories->first(fn ($c) => Str::lower($c->category_name) === Str::lower($name));

        if ($existing) {
            $this->selectCategory($index, $existing);

            return ['status' => 'exists', 'id' => $existing->id, 'name' => $existing->category_name];
        }

        // 2) Looks like one that exists (typo, plural, same meaning)? Ask "Did you mean ...?"
        if (! $force) {
            $similar = app(CategoryMatcher::class)->findSimilar(
                $name,
                $categories,
                // At most 15 AI checks per minute per user, to protect your Gemini quota.
                fn () => RateLimiter::attempt('category-match:' . Auth::id(), 15, fn () => true, 60)
            );

            if ($similar) {
                return [
                    'status' => 'suggest',
                    'id' => $similar->id,
                    'name' => $similar->category_name,
                    'typed' => $name,
                ];
            }
        }

        // 3) Keep the list tidy.
        if (Category::where('user_id', Auth::id())->count() >= 30) {
            return ['status' => 'invalid', 'message' => 'You can create up to 30 custom categories.'];
        }

        // 4) Save the new category (visible only to this user).
        try {
            $category = Category::create([
                'user_id' => Auth::id(),
                'category_name' => $name,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Created a moment ago (for example a double click): use that one.
            $category = Category::where('user_id', Auth::id())->where('category_name', $name)->firstOrFail();
        }

        $this->selectCategory($index, $category);

        return ['status' => 'created', 'id' => $category->id, 'name' => $category->category_name];
    }


    //  RECEIPT SCANNING

    public function parseReceipt(string $text): array
    {
        $this->scanMessage = null;

        // The AI call can be slow; don't let PHP's default time limit kill the request.
        if (function_exists('set_time_limit')) {
            set_time_limit(120);
        }

        // Scanning is only for the "add expense" form.
        if ($this->showEditModal) {
            return ['ok' => false, 'message' => 'Receipts can only be scanned on the add-expense form.'];
        }

        // Protects the Gemini quota: max 10 scans per minute per user.
        if (! RateLimiter::attempt('receipt-scan:' . Auth::id(), 10, fn () => true, 60)) {
            return ['ok' => false, 'message' => 'Too many scans in a short time. Please wait a minute and try again.'];
        }

        $text = Str::limit(trim($text), 6000, '');

        if (mb_strlen($text) < 20) {
            return ['ok' => false, 'message' => "Couldn't read any text from that image. Try a clearer, well-lit, flat photo."];
        }

        $categories = $this->allCategories();
        $result = app(ReceiptParser::class)->parse($text, $categories);

        if (! $result) {
            return ['ok' => false, 'message' => "Couldn't pick out the items automatically. You can add them manually."];
        }

        if ($result['merchant'] !== '') {
            $this->description = $result['merchant'];
        }

        if ($result['date']) {
            $this->expense_date = $result['date'];
        }

        $this->items = collect($result['items'])
            ->map(fn ($row) => ['uid' => (string) Str::uuid()] + $row)
            ->all();

        $this->printedTotal = $result['printed_total'];

        $this->scanMessage = 'Receipt scanned. Please check the items, categories and amounts before saving.';

        $total = $this->itemsTotal();

        // Shown by the scanning animation before it returns to the form.
        return [
            'ok'            => true,
            'merchant'      => $this->description,
            'total'         => number_format($total, 2),
            'printed_total' => $this->printedTotal !== null ? number_format($this->printedTotal, 2) : null,
            'matches'       => $this->printedTotal === null ? null : abs($this->printedTotal - $total) < 0.01,
            'items'         => collect($this->items)->map(fn ($item) => [
                'name'     => $item['description'],
                'amount'   => number_format((float) $item['amount'], 2),
                'category' => $categories->firstWhere('id', $item['category_id'])?->category_name,
            ])->all(),
        ];
    }


    //  CREATE


    public function saveExpense()
    {
        $this->validate();

        // Expense + items are saved together or not at all.
        DB::transaction(function () {
            $expense = Expense::create([
                'user_id' => Auth::id(),
                'description' => $this->description,
                'amount' => $this->itemsTotal(), // total = sum of the items
                'expense_date' => $this->expense_date,
                'notes' => $this->notes,
            ]);

            $expense->items()->createMany($this->itemRows());
        });

        session()->flash('success', 'Expense added successfully.');

        $this->resetForm();
    }


    //  EDIT


    public function editExpense($id)
    {
        $expense = Expense::with('items')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $this->editingExpenseId = $expense->id;

        // A previous receipt scan has nothing to do with the expense being edited.
        $this->printedTotal = null;
        $this->scanMessage = null;

        $this->description = $expense->description;
        $this->expense_date = $expense->expense_date;
        $this->notes = $expense->notes;

        $this->items = $expense->items->map(fn ($item) => [
            'uid' => (string) Str::uuid(),
            'description' => $item->description,
            'category_id' => $item->category_id,
            'amount' => $item->amount,
            'category_source' => $item->category_source ?? 'manual',
            'ai_suggested_category_id' => $item->ai_suggested_category_id,
            'ai_confidence' => $item->ai_confidence,
        ])->all();

        if (empty($this->items)) {
            $this->items = [$this->blankItem()];
        }

        $this->showEditModal = true;
    }

    public function updateExpense()
    {
        $this->validate();

        DB::transaction(function () {
            $expense = Expense::where('user_id', Auth::id())
                ->findOrFail($this->editingExpenseId);

            $expense->update([
                'description' => $this->description,
                'amount' => $this->itemsTotal(),
                'expense_date' => $this->expense_date,
                'notes' => $this->notes,
            ]);

            // Simplest safe way to apply added / removed / changed items.
            // The AI suggestion data travels with each row, so nothing is lost.
            $expense->items()->delete();
            $expense->items()->createMany($this->itemRows());
        });

        session()->flash('success', 'Expense updated successfully.');

        $this->showEditModal = false;
        $this->editingExpenseId = null;

        $this->resetForm();
    }

    public function cancelEdit()
    {
        $this->showEditModal = false;
        $this->showEditUnsavedModal = false;
        $this->editingExpenseId = null;

        $this->resetForm();
    }

    public function discardEditChanges()
    {
        $this->showEditUnsavedModal = false;
        $this->showEditModal = false;
        $this->editingExpenseId = null;

        $this->resetForm();
    }


    //  DELETE


    public function confirmDelete($id)
    {
        $this->deleteExpenseId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteExpense()
    {
        // Items are removed automatically (cascade on expense_items.expense_id).
        Expense::where('user_id', Auth::id())
            ->findOrFail($this->deleteExpenseId)
            ->delete();

        $this->deleteExpenseId = null;
        $this->showDeleteModal = false;

        session()->flash('success', 'Expense deleted successfully.');
    }


    //  UNSAVED MODALS


    public function closeUnsavedModal()
    {
        $this->showUnsavedModal = false;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteExpenseId = null;
    }


    //  RESET FORM


    private function resetForm()
    {
        $this->reset([
            'description',
            'notes',
            'printedTotal',
            'scanMessage',
        ]);

        $this->items = [$this->blankItem()];
        $this->expense_date = now()->toDateString();

        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showUnsavedModal = false;
        $this->showEditUnsavedModal = false;
    }


    //  RENDER


    public function render()
    {
        return view('livewire.expenses.index', [
            'expenses' => Expense::with('items.category')
                ->where('user_id', Auth::id())
                ->latest()
                ->get(),

            'categories' => $this->allCategories(),

            'total' => $this->itemsTotal(),
        ])->layout('layouts.app');
    }
}
<?php

namespace App\Livewire\Expenses;

use Livewire\Component;
use App\Models\Expense;
use App\Models\Category;
use App\Services\AI\ExpenseCategorizer; // AI: was missing
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    // Form Fields

    public $category_id = '';
    public $description = '';
    public $amount = '';
    public $expense_date = '';
    public $notes = '';


    // IDs

    public $editingExpenseId = null;
    public $deleteExpenseId = null;


    // Modal States

    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showUnsavedModal = false;
    public $showEditUnsavedModal = false;


    // AI category suggestion state

    // 'manual' | 'history' | 'ai'
    public string $categorySource = 'manual';
    public ?int $aiSuggestedCategoryId = null;
    public ?float $aiConfidence = null;

    public function mount()
    {
        $this->expense_date = now()->toDateString();
    }

    protected function rules()
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }


    //  CREATE


    public function saveExpense()
    {
        $this->validate();

        Expense::create([
            'user_id' => Auth::id(),
            'category_id' => $this->category_id,
            'description' => $this->description,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date,
            'notes' => $this->notes,

            // AI: how the category was chosen + what the AI suggested
            'category_source' => $this->categorySource,
            'ai_suggested_category_id' => $this->aiSuggestedCategoryId,
            'ai_confidence' => $this->aiConfidence,
        ]);

        session()->flash('success', 'Expense added successfully.');

        $this->resetForm();
    }


    //  EDIT


    public function editExpense($id)
    {
        $expense = Expense::where('user_id', Auth::id())
            ->findOrFail($id);

        $this->editingExpenseId = $expense->id;

        $this->category_id = $expense->category_id;
        $this->description = $expense->description;
        $this->amount = $expense->amount;
        $this->expense_date = $expense->expense_date;
        $this->notes = $expense->notes;

        // AI: never auto-suggest while editing an existing expense
        $this->categorySource = 'manual';
        $this->aiSuggestedCategoryId = null;
        $this->aiConfidence = null;

        $this->showEditModal = true;
    }

    public function updateExpense()
    {
        $this->validate();

        $expense = Expense::where('user_id', Auth::id())
            ->findOrFail($this->editingExpenseId);

        $data = [
            'category_id' => $this->category_id,
            'description' => $this->description,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date,
            'notes' => $this->notes,
        ];

        // AI: if the user changed the category, it is now a manual choice
        // (ai_suggested_category_id is kept so accuracy can still be measured)
        if ((int) $expense->category_id !== (int) $this->category_id) {
            $data['category_source'] = 'manual';
        }

        $expense->update($data);

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

    //  AI CATEGORY SUGGESTION

    public function updatedDescription(): void
    {
        if ($this->showEditModal) {
            return;
        }

        if (filled($this->category_id) && $this->categorySource === 'manual') {
            return;
        }

        $suggestion = app(ExpenseCategorizer::class)->suggest(
            Auth::id(),
            $this->description,
            $this->allCategories(),
        );

        if ($suggestion) {
            $this->category_id           = $suggestion['category_id'];
            $this->categorySource        = $suggestion['source'];
            $this->aiSuggestedCategoryId = $suggestion['source'] === 'ai' ? $suggestion['category_id'] : null;
            $this->aiConfidence          = $suggestion['source'] === 'ai' ? $suggestion['confidence'] : null;
        } elseif ($this->categorySource !== 'manual') {
            $this->reset('category_id', 'categorySource', 'aiSuggestedCategoryId', 'aiConfidence');
        }
    }

    public function updatedCategoryId(): void
    {
        $this->categorySource = 'manual';
    }

    protected function allCategories()
    {
        return Category::orderBy('category_name')->get();
    }


    //  DELETE


    public function confirmDelete($id)
    {
        $this->deleteExpenseId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteExpense()
    {
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
            'category_id',
            'description',
            'amount',
            'notes',

            // AI: clear suggestion state so it doesn't leak into the next expense
            'categorySource',
            'aiSuggestedCategoryId',
            'aiConfidence',
        ]);

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
            'expenses' => Expense::with('category')
                ->where('user_id', Auth::id())
                ->latest()
                ->get(),

            'categories' => $this->allCategories(),
        ])->layout('layouts.app');
    }
}
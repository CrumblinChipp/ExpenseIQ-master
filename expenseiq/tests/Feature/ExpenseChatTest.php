<?php

namespace Tests\Feature;

use App\Livewire\Chat;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\User;
use App\Services\AI\ExpenseContextBuilder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.key' => 'test-key']);
        Cache::flush();

        $this->seed(CategorySeeder::class);
    }

    private function makeUser(string $email = 'nicoletest@gmail.com'): User
    {
        return User::create([
            'first_name'     => 'nicole',
            'last_name'      => 'rafols',
            'email'          => $email,
            'password'       => bcrypt('Nicoletest123_'),
            'monthly_budget' => 10000,
            'is_onboarded'   => true,
        ]);
    }

    /**
     * Creates one purchase (expense) with its items.
     *
     * @param array<int, array{0: string, 1: string, 2: float}> $items  [category name, item description, amount]
     */
    private function addPurchase(User $user, string $description, array $items): void
    {
        $expense = Expense::create([
            'user_id'      => $user->id,
            'description'  => $description,
            'amount'       => collect($items)->sum(fn ($item) => $item[2]),
            'expense_date' => now()->toDateString(),
        ]);

        foreach ($items as [$category, $name, $amount]) {
            ExpenseItem::create([
                'expense_id'  => $expense->id,
                'category_id' => Category::where('category_name', $category)->value('id'),
                'description' => $name,
                'amount'      => $amount,
            ]);
        }
    }

    /** Builds a response shaped like Gemini's generateContent reply. */
    private function geminiText(string $text): array
    {
        return [
            'candidates'    => [['content' => ['parts' => [['text' => $text]]]]],
            'usageMetadata' => ['promptTokenCount' => 100, 'candidatesTokenCount' => 10],
        ];
    }

    #[Test]
    public function context_contains_correct_totals_average_and_highest_category(): void
    {
        $user = $this->makeUser();

        // Purchase 1: a receipt with two items in different categories (350)
        $this->addPurchase($user, 'SM receipt', [
            ['Food & Drinks', 'Burger', 100],
            ['Grocery', 'Groceries', 250],
        ]);
        // Purchase 2: a single item (50)
        $this->addPurchase($user, 'Grab ride', [
            ['Transportation', 'Grab to work', 50],
        ]);

        $context = app(ExpenseContextBuilder::class)->build($user->id);
        $month   = $context['this_month'];

        $this->assertEquals(400.0, $month['total_spent']);
        $this->assertSame(2, $month['transactions']);
        $this->assertSame(3, $month['items']);
        $this->assertEquals(200.0, $month['average_expense']);        // 400 / 2 purchases
        $this->assertEquals(133.33, $month['average_item_amount']);   // 400 / 3 items
        $this->assertSame('Grocery', $month['highest_category']);
        $this->assertEquals(10000.0, $month['monthly_budget']);

        $this->assertCount(6, $month['by_category']); // all seeded categories, even with 0 spent
        $this->assertSame('Grocery', $month['by_category'][0]['category']);
        $this->assertEquals(250.0, $month['by_category'][0]['spent']);

        $this->assertSame('SM receipt', $month['largest_purchases'][0]['description']);
        $this->assertEquals(350.0, $month['largest_purchases'][0]['amount']);
        $this->assertSame('Groceries', $month['largest_items'][0]['description']);

        $this->assertEquals(400.0, array_values($context['monthly_totals_last_6'])[5]); // current month is last
    }

    #[Test]
    public function context_never_includes_other_users_data(): void
    {
        $user  = $this->makeUser();
        $other = $this->makeUser('other@gmail.com');

        $this->addPurchase($user, 'Supermarket', [['Grocery', 'Rice', 500]]);
        $this->addPurchase($other, 'SECRET-PURCHASE', [['Grocery', 'SECRET-ITEM', 9999]]);

        $context = app(ExpenseContextBuilder::class)->build($user->id);

        $this->assertEquals(500.0, $context['this_month']['total_spent']);
        $this->assertStringNotContainsString('SECRET', json_encode($context));
    }

    #[Test]
    public function chat_sends_the_users_data_and_shows_the_reply(): void
    {
        $user  = $this->makeUser();
        $other = $this->makeUser('other@gmail.com');

        $this->addPurchase($user, 'Monthly Rent', [['Housing', 'Rent', 8000]]);
        $this->addPurchase($other, 'SECRET-PURCHASE', [['Housing', 'SECRET-ITEM', 9999]]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(
                $this->geminiText('Your highest category is Housing.')
            ),
        ]);

        $this->actingAs($user);

        Livewire::test(Chat::class)
            ->set('input', 'Which category is highest?')
            ->call('send')
            ->assertSee('Which category is highest?')
            ->assertSee('Your highest category is Housing.')
            ->assertSet('input', '');

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            $system = $request['systemInstruction']['parts'][0]['text'];

            return str_contains($system, 'highest_category')
                && str_contains($system, 'Monthly Rent')
                && ! str_contains($system, 'SECRET');
        });
    }

    #[Test]
    public function chat_shows_a_friendly_error_when_the_quota_is_reached(): void
    {
        $user = $this->makeUser();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota exceeded']], 429),
        ]);

        $this->actingAs($user);

        Livewire::test(Chat::class)
            ->set('input', 'What is my average expense?')
            ->call('send')
            ->assertSee('busy')
            ->assertSet('input', 'What is my average expense?') // restored so the user can retry
            ->assertSet('messages', []);
    }

    #[Test]
    public function chat_rejects_an_empty_message_without_calling_the_ai(): void
    {
        $user = $this->makeUser();

        Http::fake();
        $this->actingAs($user);

        Livewire::test(Chat::class)
            ->set('input', '')
            ->call('send')
            ->assertHasErrors(['input' => 'required']);

        Http::assertNothingSent();
    }

    #[Test]
    public function conversation_follows_the_user_to_other_pages(): void
    {
        $user = $this->makeUser();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(
                $this->geminiText('Your highest category is Housing.')
            ),
        ]);

        $this->actingAs($user);

        Livewire::test(Chat::class)
            ->set('input', 'Which category is highest?')
            ->call('send');

        // A new page load creates a new component instance, which must restore the conversation.
        Livewire::test(Chat::class)
            ->assertSee('Which category is highest?')
            ->assertSee('Your highest category is Housing.');
    }

    #[Test]
    public function clearing_the_chat_also_clears_the_saved_conversation(): void
    {
        $user = $this->makeUser();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiText('Hello!')),
        ]);

        $this->actingAs($user);

        Livewire::test(Chat::class)
            ->set('input', 'Hi')
            ->call('send')
            ->call('clearChat')
            ->assertSet('messages', []);

        Livewire::test(Chat::class)->assertSet('messages', []);
    }

    #[Test]
    public function guest_cannot_send_messages(): void
    {
        Http::fake();

        Livewire::test(Chat::class)
            ->set('input', 'Hello')
            ->call('send')
            ->assertForbidden();

        Http::assertNothingSent();
    }
}
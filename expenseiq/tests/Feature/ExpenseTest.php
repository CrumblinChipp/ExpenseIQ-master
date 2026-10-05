<?php
namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Expense;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_add_expense(): void
    {
        // Arrange
        $this->seed(CategorySeeder::class);

        $user = User::create([
            'first_name' => 'nicole',
            'last_name' => 'rafols',
            'email' => 'nicole.rafols@example.com',
            'password' => bcrypt('Nicoletest123_'),
            'is_onboarded' => true,
        ]);

        $category = Category::where(
            'category_name',
            'Housing'
        )->first();

        // Act
        $response = $this->actingAs($user)->post('/expenses', [
            'category_id' => $category->id,
            'description' => 'Monthly Rent',
            'amount' => 10000,
            'expense_date' => now()->toDateString(),
            'notes' => 'rent payment',
        ]);

        // Assert
        $response->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Monthly Rent',
            'amount' => 10000,
        ]);
    }

    // can view expense
    #[Test]
    public function user_can_view_expense(): void
    {
        // Arrange
        $this->seed(CategorySeeder::class);

        $user = User::create([
            'first_name' => 'nicole',
            'last_name' => 'rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => bcrypt('Nicoletest123_'),
            'is_onboarded' => true,
        ]);

        $response = $this->actingAs($user)->get('/expenses');

        $response->assertStatus(200);
    }

    // can update expense
    #[Test]
    public function user_can_update_expense(): void
    {
        // Arrange
        $this->seed(CategorySeeder::class);

        $user = User::create([
            'first_name' => 'nicole',
            'last_name' => 'rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => bcrypt('Nicoletest123_'),
            'is_onboarded' => true,
        ]);

        $category = Category::where('category_name', 'Housing')->first();

        $expense = Expense::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Monthly Rent',
            'amount' => 10000,
            'expense_date' => now()->toDateString(),
            'notes' => 'old note',
        ]);

        $response = $this->actingAs($user)->put("/expenses/{$expense->id}", [
            'category_id' => $category->id,
            'description' => 'Updated Rent',
            'amount' => 12000,
            'expense_date' => now()->toDateString(),
            'notes' => 'updated note',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'Updated Rent',
            'amount' => 12000,
            'notes' => 'updated note',
        ]);
    }

    // can delete expense
    #[Test]
    public function user_can_delete_expense(): void
    {
        // Arrange
        $this->seed(CategorySeeder::class);

        $user = User::create([
            'first_name' => 'nicole',
            'last_name' => 'rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => bcrypt('Nicoletest123_'),
            'is_onboarded' => true,
        ]);

        $category = Category::where('category_name', 'Housing')->first();
        
        $expense = Expense::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => 'Monthly Rent',
            'amount' => 10000,
            'expense_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->delete("/expenses/{$expense->id}");

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }
}
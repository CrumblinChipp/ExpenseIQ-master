<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    // User can save budget
    #[Test]
    public function user_can_save_budget(): void
    {
        // Arrange
        $this->seed(CategorySeeder::class);

        $user = User::create([
            'first_name' => 'Nicole',
            'last_name' => 'Rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => bcrypt('Nicoletest123_'),
            'monthly_budget' => 50000,
            'is_onboarded' => true,
        ]);

        $category = Category::where('category_name', 'Housing')->first();

        // Act
        $response = $this->actingAs($user)->post('/budget/save', [
            'category_id' => [
                $category->id,
            ],
            'budget_amount' => [
                10000,
            ],
        ]);

        // Assert
        $response->assertRedirect(route('budget.index'));

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => now()->month,
            'year' => now()->year,
            'budget_amount' => 10000,
        ]);
    }

    // User cannot exceed monthly budget
    #[Test]
    public function user_cannot_exceed_monthly_budget(): void
    {
        // Arrange
        $this->seed(CategorySeeder::class);

        $user = User::create([
            'first_name' => 'Nicole',
            'last_name' => 'Rafols',
            'email' => 'nicoletest@gmail.com',
            'password' => bcrypt('Nicoletest123_'),
            'monthly_budget' => 10000, // Lower than allocated budget
            'is_onboarded' => true,
        ]);

        $category = Category::where('category_name', 'Housing')->first();

        // Act
        $response = $this->actingAs($user)->from('/budget')->post('/budget/save', [
            'category_id' => [
                $category->id,
            ],
            'budget_amount' => [
                15000,
            ],
        ]);

        // Assert
        $response->assertRedirect('/budget');

        $response->assertSessionHasErrors('budget');

        $this->assertDatabaseMissing('budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'month' => now()->month,
            'year' => now()->year,
            'budget_amount' => 15000,
        ]);
    }
}
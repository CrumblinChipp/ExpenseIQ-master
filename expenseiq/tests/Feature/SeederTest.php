<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function category_seeder_populates_the_database(): void
    {
        // arrange
        $this->seed(CategorySeeder::class);

        // assert
        $this->assertDatabaseHas('categories', [
            'category_name' => 'Housing',
        ]);

        $this->assertDatabaseHas('categories', [
            'category_name' => 'Housing',
        ]);

        $this->assertDatabaseHas('categories', [
            'category_name' => 'Food & Drinks',
        ]);

        $this->assertDatabaseHas('categories', [
            'category_name' => 'Grocery',
        ]);

        $this->assertDatabaseHas('categories', [
            'category_name' => 'Transportation',
        ]);

        $this->assertDatabaseHas('categories', [
            'category_name' => 'Health',
        ]);

        $this->assertDatabaseHas('categories', [
            'category_name' => 'Entertainment',
        ]);
    }
}

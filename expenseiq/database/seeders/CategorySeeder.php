<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['category_name' => 'Housing'],
            ['category_name' => 'Food & Drinks'],
            ['category_name' => 'Grocery'],
            ['category_name' => 'Transportation'],
            ['category_name' => 'Health'],
            ['category_name' => 'Entertainment'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate([
                'category_name' => $category['category_name'],
            ]);
        }
    }
}
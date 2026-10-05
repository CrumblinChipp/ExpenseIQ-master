<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;

class BudgetWarningService
{
    public function getWarnings(int $userId): array
    {
        $warnings = [];

        $categories = Category::orderBy('id')->get();

        foreach ($categories as $category) {

            $spent = Expense::where('user_id', $userId)
                ->where('category_id', $category->id)
                ->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount');

            $budget = Budget::where('user_id', $userId)
                ->where('category_id', $category->id)
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->value('budget_amount') ?? 0;

            if ($budget <= 0) {
                continue;
            }

            $percentage = ($spent / $budget) * 100;

            if ($percentage >= 80) {

                $warnings[] = [

                    'name' => $category->category_name,

                    'percentage' => round($percentage),

                ];

            }
        }

        return $warnings;
    }
}
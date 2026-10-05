<?php

namespace App\Services;

use App\Services\BudgetWarningService;
use App\Models\User;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;


class DashboardService
{
    protected BudgetWarningService $warningService;

    public function __construct(BudgetWarningService $warningService)
    {
        $this->warningService = $warningService;
    }

    public function getDashboardData(int $userId): array
    {
        $user = User::findOrFail($userId);

        // Current Month Expenses
        $expenses = Expense::where('user_id', $userId)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);

        $totalExpenses = $expenses->sum('amount');

        // Overall Monthly Budget (from users table)
        $totalBudget = $user->monthly_budget ?? 0;

        $remainingBudget = max($totalBudget - $totalExpenses, 0);

        $expenseCount = $expenses->count();

        $recentExpenses = Expense::with('category')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $budgetPercentage = $totalBudget > 0
            ? ($totalExpenses / $totalBudget) * 100
            : 0;
        /*
        |--------------------------------------------------------------------------
        | Category Chart & Budget Progress
        |--------------------------------------------------------------------------
        */

        $categories = Category::orderBy('id')->get();

        $chartLabels = [];
        $chartData = [];
        $budgetProgress = [];

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

            $percentage = $budget > 0
            ? min(($spent / $budget) * 100, 100)
            : 0;

            $chartLabels[] = $category->category_name;
            $chartData[] = $spent;

            $budgetProgress[] = [
                'name' => $category->category_name,
                'spent' => $spent,
                'budget' => $budget,
                'percentage' => $percentage,
            ];
        }

        $warnings = $this->warningService->getWarnings($userId);

        return [
            'totalExpenses' => $totalExpenses,
            'totalBudget' => $totalBudget,
            'remainingBudget' => $remainingBudget,
            'expenseCount' => $expenseCount,
            'recentExpenses' => $recentExpenses,
            'budgetPercentage' => $budgetPercentage,
            'warnings' => $warnings,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'budgetProgress' => $budgetProgress,
        ];
    }
}
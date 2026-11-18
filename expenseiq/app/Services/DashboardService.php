<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseItem;

class DashboardService
{
    public function getDashboardData(int $userId): array
    {
        $month = fn () => Expense::where('user_id', $userId)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);

        // Category totals come from the items, not from the expense header.
        $byCategory = ExpenseItem::query()
            ->join('expenses', 'expenses.id', '=', 'expense_items.expense_id')
            ->where('expenses.user_id', $userId)
            ->whereMonth('expenses.expense_date', now()->month)
            ->whereYear('expenses.expense_date', now()->year)
            ->selectRaw('expense_items.category_id, SUM(expense_items.amount) as total')
            ->groupBy('expense_items.category_id')
            ->pluck('total', 'category_id');

        $categories = Category::visibleTo($userId)->orderBy('id')->get();

        return [
            'totalExpenses'  => $month()->sum('amount'),
            'expenseCount'   => $month()->count(),
            'largestExpense' => $month()->max('amount') ?? 0,
            'recentExpenses' => Expense::with('items.category')
                ->where('user_id', $userId)->latest()->take(5)->get(),
            'chartLabels'    => $categories->pluck('category_name')->all(),
            'chartData'      => $categories->map(fn ($c) => (float) ($byCategory[$c->id] ?? 0))->all(),
        ];
    }
}
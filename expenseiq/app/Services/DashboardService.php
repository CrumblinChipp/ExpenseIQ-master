<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;

class DashboardService
{
    public function getDashboardData(int $userId): array
    {
        $month = Expense::where('user_id', $userId)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);

        $byCategory = (clone $month)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $categories = Category::orderBy('id')->get();

        return [
            'totalExpenses'  => (clone $month)->sum('amount'),
            'expenseCount'   => (clone $month)->count(),
            'largestExpense' => (clone $month)->max('amount') ?? 0,
            'recentExpenses' => Expense::with('category')
                ->where('user_id', $userId)->latest()->take(5)->get(),
            'chartLabels'    => $categories->pluck('category_name')->all(),
            'chartData'      => $categories->map(fn ($c) => (float) ($byCategory[$c->id] ?? 0))->all(),
        ];
    }
}
<?php

namespace App\Services\AI;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Builds the small, pre-computed summary of ONE user's spending that the chat
 * assistant is allowed to see. The numbers are calculated here (SQL/PHP), not by the model,
 * so averages and totals are always correct and the prompt stays small.
 *
 * Data model:
 *   expenses       = one purchase / receipt (date, title)
 *   expense_items  = the lines of that purchase, each with its own category and amount
 * All money figures are summed from expense_items (the single source of truth).
 *
 * Works on SQLite (tests) and MySQL (app): no DB-specific functions.
 */
class ExpenseContextBuilder
{
    public function build(int $userId): array
    {
        $now = now();

        // Items belonging to this user, in the current month. Fresh query each call (builders are mutable).
        $monthItems = fn () => ExpenseItem::query()
            ->join('expenses', 'expenses.id', '=', 'expense_items.expense_id')
            ->where('expenses.user_id', $userId)
            ->whereYear('expenses.expense_date', $now->year)
            ->whereMonth('expenses.expense_date', $now->month);

        // Spent, item count and purchase count per category.
        $perCategory = $monthItems()
            ->join('categories', 'categories.id', '=', 'expense_items.category_id')
            ->selectRaw(
                'categories.category_name AS category, '
                . 'SUM(expense_items.amount) AS total, '
                . 'COUNT(*) AS items, '
                . 'COUNT(DISTINCT expense_items.expense_id) AS transactions'
            )
            ->groupBy('categories.category_name')
            ->get()
            ->keyBy('category');

        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->with('category')
            ->get()
            ->mapWithKeys(fn ($b) => [$b->category->category_name => (float) $b->budget_amount]);

        // Every category is listed (even with 0 spent) so the model sees the full picture.
        $categories = Category::orderBy('category_name')->get()
            ->map(function ($category) use ($perCategory, $budgets) {
                $row    = $perCategory->get($category->category_name);
                $spent  = round((float) ($row->total ?? 0), 2);
                $budget = $budgets[$category->category_name] ?? null;

                return [
                    'category'            => $category->category_name,
                    'spent'               => $spent,
                    'items'               => (int) ($row->items ?? 0),
                    'transactions'        => (int) ($row->transactions ?? 0),
                    'budget'              => $budget,
                    'budget_used_percent' => $budget ? round($spent / $budget * 100, 1) : null,
                ];
            })
            ->sortByDesc('spent')
            ->values();

        $totalSpent   = round((float) $monthItems()->sum('expense_items.amount'), 2);
        $itemCount    = (int) $monthItems()->count();
        $transactions = (int) $monthItems()->distinct()->count('expense_items.expense_id');
        $highest      = $categories->first();

        return [
            'currency' => 'PHP (₱)',
            'today'    => $now->toDateString(),

            'this_month' => [
                'month'               => $now->format('F Y'),
                'total_spent'         => $totalSpent,
                'transactions'        => $transactions,   // purchases / receipts
                'items'               => $itemCount,      // individual lines inside them
                'average_expense'     => $transactions ? round($totalSpent / $transactions, 2) : 0, // per transaction
                'average_item_amount' => $itemCount ? round($totalSpent / $itemCount, 2) : 0,       // per item
                'monthly_budget'      => (float) (User::find($userId)?->monthly_budget ?? 0),
                'highest_category'    => ($highest && $highest['spent'] > 0) ? $highest['category'] : null,
                'by_category'         => $categories->all(),
                'largest_purchases'   => $this->largestPurchases($monthItems),
                'largest_items'       => $this->largestItems($monthItems),
            ],

            // Oldest -> newest, current month last, missing months filled with 0.
            'monthly_totals_last_6' => $this->lastSixMonths($userId),
        ];
    }

    /** Top 5 whole purchases (all items of an expense added together). */
    private function largestPurchases(callable $monthItems): array
    {
        $totals = $monthItems()
            ->selectRaw('expense_items.expense_id AS expense_id, SUM(expense_items.amount) AS total')
            ->groupBy('expense_items.expense_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $expenses = Expense::whereIn('id', $totals->pluck('expense_id'))->get()->keyBy('id');

        return $totals->map(function ($row) use ($expenses) {
            $expense = $expenses->get($row->expense_id);

            return [
                'description' => Str::limit($expense?->description ?? 'Expense', 60),
                'amount'      => round((float) $row->total, 2),
                'date'        => $expense?->expense_date?->toDateString(),
            ];
        })->all();
    }

    /** Top 5 single items. */
    private function largestItems(callable $monthItems): array
    {
        return $monthItems()
            ->select('expense_items.*')
            ->with(['category', 'expense'])
            ->orderByDesc('expense_items.amount')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'description' => Str::limit($item->description, 60),
                'amount'      => round((float) $item->amount, 2),
                'category'    => $item->category?->category_name,
                'purchase'    => Str::limit($item->expense?->description ?? '', 60),
                'date'        => $item->expense?->expense_date?->toDateString(),
            ])
            ->all();
    }

    /** @return array<string, float> e.g. ['2026-06' => 0, ..., '2026-11' => 3450.5] */
    private function lastSixMonths(int $userId): array
    {
        $start  = now()->startOfMonth()->subMonths(5);
        $totals = [];

        for ($i = 0; $i < 6; $i++) {
            $totals[$start->copy()->addMonths($i)->format('Y-m')] = 0.0;
        }

        ExpenseItem::query()
            ->join('expenses', 'expenses.id', '=', 'expense_items.expense_id')
            ->where('expenses.user_id', $userId)
            ->where('expenses.expense_date', '>=', $start->toDateString())
            ->get(['expenses.expense_date', 'expense_items.amount'])
            ->each(function ($row) use (&$totals) {
                $key = Carbon::parse($row->expense_date)->format('Y-m');

                if (array_key_exists($key, $totals)) {
                    $totals[$key] += (float) $row->amount;
                }
            });

        return array_map(fn ($v) => round($v, 2), $totals);
    }
}
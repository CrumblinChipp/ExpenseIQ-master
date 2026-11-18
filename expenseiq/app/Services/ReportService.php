<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ExpenseItem;
use Carbon\Carbon;

class ReportService
{
    /** Items of one user, joined to their purchase, optionally limited to one category. */
    private function itemsQuery(int $userId, $category = 'all')
    {
        return ExpenseItem::query()
            ->join('expenses', 'expenses.id', '=', 'expense_items.expense_id')
            ->where('expenses.user_id', $userId)
            ->when(
                filled($category) && $category !== 'all',
                fn ($q) => $q->where('expense_items.category_id', $category)
            );
    }

    public function getMonthlyReport(
        int $userId,
        string $month,
        int $year,
        $category = 'all'
    ): array {
        $selectedMonth = $month === 'all' ? now()->month : (int) $month;

        // One query: every item of the year.
        $rows = $this->itemsQuery($userId, $category)
            ->whereYear('expenses.expense_date', $year)
            ->get([
                'expenses.expense_date as expense_date',
                'expense_items.category_id as category_id',
                'expense_items.amount as amount',
            ])
            ->map(fn ($row) => [
                'date'        => Carbon::parse($row->expense_date),
                'category_id' => $row->category_id,
                'amount'      => (float) $row->amount,
            ]);

        // Yearly monthly totals
        $monthlyTotals = array_fill(1, 12, 0.0);

        foreach ($rows as $row) {
            $monthlyTotals[$row['date']->month] += $row['amount'];
        }

        $monthlyTotals = array_map(fn ($v) => round($v, 2), $monthlyTotals);

        // Rows of the selected month (current month when "all")
        $monthRows     = $rows->filter(fn ($r) => $r['date']->month === $selectedMonth);
        $totalExpenses = round($monthRows->sum('amount'), 2);

        // Summary cards
        $averageMonthly     = collect($monthlyTotals)->average();
        $highestMonthAmount = max($monthlyTotals);
        $highestMonthNumber = array_search($highestMonthAmount, $monthlyTotals);

        $bestMonth = $highestMonthAmount > 0
            ? Carbon::create($year, $highestMonthNumber, 1)->format('F')
            : '-';

        $yearTotal = array_sum($monthlyTotals);

        // Chart data
        $chartLabels = [];
        $chartData   = [];

        if ($month === 'all') {
            foreach ($monthlyTotals as $monthNumber => $amount) {
                $chartLabels[] = Carbon::create($year, $monthNumber, 1)->format('M');
                $chartData[]   = $amount;
            }
        } else {
            $daysInMonth = Carbon::create($year, $selectedMonth, 1)->daysInMonth;

            $perDay = $monthRows
                ->groupBy(fn ($r) => $r['date']->day)
                ->map(fn ($group) => $group->sum('amount'));

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $chartLabels[] = $day;
                $chartData[]   = round($perDay[$day] ?? 0, 2);
            }
        }

        // Top categories
        $names = Category::pluck('category_name', 'id');

        $categorySummary = $monthRows
            ->groupBy('category_id')
            ->mapWithKeys(fn ($group, $id) => [
                ($names[$id] ?? 'Uncategorized') => round($group->sum('amount'), 2),
            ])
            ->sortDesc();

        $highestCategory = $categorySummary->max();
        $topCategories   = [];

        foreach ($categorySummary as $name => $amount) {
            $topCategories[] = [
                'name'       => $name,
                'amount'     => $amount,
                'percentage' => $highestCategory > 0
                    ? round(($amount / $highestCategory) * 100)
                    : 0,
            ];
        }

        // Month comparison
        $monthComparison = [];

        if ($month !== 'all') {
            for ($i = $selectedMonth - 1; $i >= max(1, $selectedMonth - 3); $i--) {
                $previous = $monthlyTotals[$i];
                $current  = $monthlyTotals[$selectedMonth];

                $monthComparison[] = [
                    'month'  => Carbon::create($year, $i, 1)->format('F'),
                    'amount' => $previous,
                    'change' => $previous > 0
                        ? round((($current - $previous) / $previous) * 100, 1)
                        : 0,
                ];
            }
        }

        return [
            'totalExpenses'   => $totalExpenses,
            'categorySummary' => $categorySummary,

            // Summary cards
            'averageMonthly'     => $averageMonthly,
            'bestMonth'          => $bestMonth,
            'highestMonthAmount' => $highestMonthAmount,
            'yearTotal'          => $yearTotal,

            // Chart
            'chartLabels' => $chartLabels,
            'chartData'   => $chartData,

            // Lists
            'topCategories'   => $topCategories,
            'monthComparison' => $monthComparison,
        ];
    }

    /**
     * Used by the CSV / PDF export.
     * 'expenses' is now a list of ITEMS (one row per item), each with its category and purchase loaded.
     */
    public function getReportByDateRange(
        int $userId,
        string $from,
        string $to,
        $category = 'all'
    ): array {
        $items = $this->itemsQuery($userId, $category)
            ->whereBetween('expenses.expense_date', [$from, $to])
            ->with(['category', 'expense'])
            ->select('expense_items.*')
            ->orderBy('expenses.expense_date')
            ->orderBy('expense_items.id')
            ->get();

        $categorySummary = $items
            ->groupBy(fn ($item) => $item->category?->category_name ?? 'Uncategorized')
            ->map(fn ($group) => $group->sum('amount'))
            ->sortDesc();

        return [
            'expenses'        => $items,
            'transactions'    => $items->pluck('expense_id')->unique()->count(),
            'totalExpenses'   => $items->sum('amount'),
            'categorySummary' => $categorySummary,
        ];
    }
}
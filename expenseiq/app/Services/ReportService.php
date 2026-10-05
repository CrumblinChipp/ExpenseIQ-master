<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use Carbon\Carbon;

class ReportService
{
    public function getMonthlyReport(
        int $userId, 
        string $month, 
        int $year,
        $category = 'all'
    ): array
    {
        // | Current Month Expenses

        $expenses = Expense::with('category')
            ->where('user_id', $userId)
            ->whereYear('expense_date', $year);

        if ($month != 'all') {
            $expenses->whereMonth('expense_date', $month);
        } else {
            $expenses->whereMonth('expense_date', now()->month);
        }

        if ($category != 'all') {
            $expenses->where('category_id', $category);
        }

        $expenses = $expenses->get();

        $totalExpenses = $expenses->sum('amount');
        
        // | Current Month Budget
        $budget = Budget::where('user_id', $userId)
            ->where('year', $year);

        if ($month != 'all') {
            $budget->where('month', $month);
        }
            $budget = $budget->get();

        $totalBudget = $budget->sum('budget_amount');

        // | Yearly Monthly Totals
        $monthlyTotals = [];

        for ($m = 1; $m <= 12; $m++) {

        $query = Expense::where('user_id', $userId)
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $m);

        if ($category != 'all') {
            $query->where('category_id', $category);
        }

        $monthlyTotals[$m] = $query->sum('amount');
    }

        // | Summary Cards

        $averageMonthly = collect($monthlyTotals)->average();
        $highestMonthAmount = max($monthlyTotals);

        $highestMonthNumber = array_search(
            $highestMonthAmount,
            $monthlyTotals
        );

        $bestMonth = $highestMonthAmount > 0
            ? Carbon::create()->month($highestMonthNumber)->format('F')
            : '-';

        $yearTotal = array_sum($monthlyTotals);

        // | Chart Data
        $chartLabels = [];
        $chartData = [];

        if ($month == 'all') {

            foreach ($monthlyTotals as $monthNumber => $amount) {

                $chartLabels[] = Carbon::create()
                    ->month($monthNumber)
                    ->format('M');

                $chartData[] = $amount;
            }

        } else {

            $daysInMonth = Carbon::create($year, (int)$month)->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {

                $query = Expense::where('user_id', $userId)
                    ->whereYear('expense_date', $year)
                    ->whereMonth('expense_date', $month)
                    ->whereDay('expense_date', $day);

                if ($category != 'all') {
                    $query->where('category_id', $category);
                }

                $chartLabels[] = $day;
                $chartData[] = $query->sum('amount');
            }
        }

        // | Top Categories

        $categorySummary = $expenses
            ->groupBy(fn ($expense) => $expense->category->category_name)
            ->map(fn ($items) => $items->sum('amount'))
            ->sortDesc();

        $highestCategory = $categorySummary->max();
        $topCategories = [];

        foreach ($categorySummary as $name => $amount) {

            $topCategories[] = [

                'name' => $name,
                'amount' => $amount,
                'percentage' => $highestCategory > 0
                    ? round(($amount / $highestCategory) * 100)
                    : 0,
            ];
        }

        //  Month Comparison
        $monthComparison = [];

        if ($month != 'all') {

            for ($i = $month - 1; $i >= max(1, $month - 3); $i--) {

                $previous = $monthlyTotals[$i];
                $current = $monthlyTotals[$month];

                $change = $previous > 0
                    ? round((($current - $previous) / $previous) * 100, 1)
                    : 0;

                $monthComparison[] = [

                    'month' => Carbon::create()
                        ->month($i)
                        ->format('F'),

                    'amount' => $previous,

                    'change' => $change,

                ];
            }
        }
        return [
            'totalBudget' => $totalBudget,
            'totalExpenses' => $totalExpenses,
            'remainingBudget' => max($totalBudget - $totalExpenses, 0),
            'categorySummary' => $categorySummary,
            'expenses' => $expenses,

            // Summary Cards
            'averageMonthly' => $averageMonthly,
            'bestMonth' => $bestMonth,
            'highestMonthAmount' => $highestMonthAmount,
            'yearTotal' => $yearTotal,

            // Chart
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,

            // Categories
            'topCategories' => $topCategories,

            // Month Comparison
            'monthComparison' => $monthComparison,

        ];

        } // <-- Dito nagtatapos ang getMonthlyReport()



        public function getReportByDateRange(
            int $userId,
            string $from,
            string $to,
            $category = 'all'
        ): array {

            $query = Expense::with('category')
                ->where('user_id', $userId)
                ->whereBetween('expense_date', [$from, $to]);

            if ($category != 'all') {
                $query->where('category_id', $category);
            }

            $expenses = $query
                ->orderBy('expense_date')
                ->get();

            $totalExpenses = $expenses->sum('amount');

            $categorySummary = $expenses
                ->groupBy(fn ($expense) => $expense->category->category_name)
                ->map(fn ($items) => $items->sum('amount'))
                ->sortDesc();

            return [

                'expenses' => $expenses,

                'totalBudget' => 0,

                'totalExpenses' => $totalExpenses,

                'remainingBudget' => 0,

                'categorySummary' => $categorySummary,

            ];
        }

        } // <-- Dito nagtatapos ang ReportService
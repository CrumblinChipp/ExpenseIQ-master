<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Services\BudgetWarningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    protected BudgetWarningService $warningService;

    public function __construct(BudgetWarningService $warningService)
    {
        $this->warningService = $warningService;
    }

    public function index()
    {
        $order = [
            'Housing',
            'Food & Drinks',
            'Grocery',
            'Transportation',
            'Health',
            'Entertainment',
        ];

        $categories = Category::all()->sortBy(function ($category) use ($order) {
            return array_search($category->category_name, $order);
        });

        $budgets = Budget::where('user_id', Auth::id())
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->get()
            ->keyBy('category_id');

        $spent = Expense::where('user_id', Auth::id())
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $monthlyBudget = Auth::user()->monthly_budget ?? 0;

        $allocatedBudget = $budgets->sum('budget_amount');

        $remainingBudget = $monthlyBudget - $allocatedBudget;

        // Reusable Warning Logic
        $warnings = $this->warningService->getWarnings(Auth::id());

        return view('livewire.budget.index', compact(
            'categories',
            'budgets',
            'spent',
            'monthlyBudget',
            'allocatedBudget',
            'remainingBudget',
            'warnings'
        ));
    }

    public function save(Request $request)
    {
        $totalAllocated = array_sum($request->budget_amount);

        if ($totalAllocated > Auth::user()->monthly_budget) {

            return back()
                ->withInput()
                ->withErrors([
                    'budget' => 'The total allocated budget cannot exceed your monthly budget.',
                ]);

        }

        foreach ($request->category_id as $index => $categoryId) {

            Budget::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'category_id' => $categoryId,
                    'month' => now()->month,
                    'year' => now()->year,
                ],
                [
                    'budget_amount' => $request->budget_amount[$index],
                ]
            );
        }

        return redirect()
            ->route('budget.index')
            ->with('success', 'Budget saved successfully.');
    }
}
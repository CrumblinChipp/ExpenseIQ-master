<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Category;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /**
     * Display all expenses of the authenticated user.
     */
    public function index()
    {
        $expenses = Expense::with('category')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $categories = Category::orderBy('category_name')->get();

        return view(
            'expenses.index',
            compact('expenses', 'categories')
        );
    }

    /**
     * Show Create Expense Form.
     */
    public function create()
    {
        $categories = Category::orderBy('category_name')->get();

        return view('expenses.create', compact('categories'));
    }

    /**
     * Store Expense.
     */
    public function store(StoreExpenseRequest $request)
    {
        $this->expenseService->store($request->validated());

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense added successfully.');
    }

    /**
     * Show Edit Form.
     */
    public function edit(Expense $expense)
    {
        abort_if($expense->user_id !== Auth::id(), 403);

        $categories = Category::orderBy('category_name')->get();

        return view('expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update Expense.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        abort_if($expense->user_id !== Auth::id(), 403);

        $this->expenseService->update(
            $expense,
            $request->validated()
        );

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Delete Expense.
     */
    public function destroy(Expense $expense)
    {
        abort_if($expense->user_id !== Auth::id(), 403);

        $this->expenseService->delete($expense);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{
    /**
     * Store a new expense.
     */
    public function store(array $data): Expense
    {
        return Expense::create([
            'user_id'       => Auth::id(),
            'category_id'   => $data['category_id'],
            'description'   => $data['description'],
            'amount'        => $data['amount'],
            'expense_date'  => $data['expense_date'],
            'notes'         => $data['notes'] ?? null,
        ]);
    }

    /**
     * Update an expense.
     */
    public function update(Expense $expense, array $data): bool
    {
        return $expense->update($data);
    }

    /**
     * Delete an expense.
     */
    public function delete(Expense $expense): bool
    {
        return $expense->delete();
    }
}
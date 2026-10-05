<?php

namespace App\Services;

use App\Models\Budget;
use Illuminate\Support\Facades\Auth;

class BudgetService
{
    /**
     * Store Budget
     */
    public function store(array $data): Budget
    {
        return Budget::create([
            'user_id' => Auth::id(),
            'category_id' => $data['category_id'],
            'month' => $data['month'],
            'year' => $data['year'],
            'budget_amount' => $data['budget_amount'],
        ]);
    }

    /**
     * Update Budget
     */
    public function update(Budget $budget, array $data): bool
    {
        return $budget->update($data);
    }

    /**
     * Delete Budget
     */
    public function delete(Budget $budget): bool
    {
        return $budget->delete();
    }
}
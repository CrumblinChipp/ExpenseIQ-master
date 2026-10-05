<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [

            'category_id' => 'required|exists:categories,id',

            'description' => 'required|string|max:255',

            'amount' => 'required|numeric|min:0.01',

            'expense_date' => 'required|date',

            'notes' => 'nullable|string|max:500',

        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [

            'category_id.required' => 'Please select a category.',

            'description.required' => 'Description is required.',

            'amount.required' => 'Amount is required.',

            'expense_date.required' => 'Expense date is required.',

        ];
    }
}
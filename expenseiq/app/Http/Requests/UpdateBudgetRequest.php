<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'category_id' => 'required|exists:categories,id',

            'month' => 'required|integer|between:1,12',

            'year' => 'required|integer|min:2025',

            'budget_amount' => 'required|numeric|min:1',

        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinancialGoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'idModule' => 'required|integer',
            'amount' => 'required|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'id_customer.required' => 'Customer id is required',
            'id_customer.integer' => 'Customer id must be integer',
            'idModule.required' => 'Module id is required',
            'idModule.integer' => 'Module id must be integer',
            'amount.required' => 'amount is required',
            'amount.integer' => 'amount must be integer'
        ];
    }
}

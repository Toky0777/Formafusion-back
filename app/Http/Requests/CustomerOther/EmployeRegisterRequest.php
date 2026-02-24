<?php

namespace App\Http\Requests\CustomerOther;

use Illuminate\Foundation\Http\FormRequest;

class EmployeRegisterRequest extends FormRequest
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
            'emp_name' => 'required|min:2|max:200',
            'email' => 'required|min:2|max:200|email|unique:users,email'
        ];
    }

    public function messages(): array
    {
        return [
            'emp_name.required' => 'Ce champs est obligatoire',
            'email.required' => 'Ce champs est obligatoire'
        ];
    }
}

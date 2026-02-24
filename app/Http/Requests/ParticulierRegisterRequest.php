<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParticulierRegisterRequest extends FormRequest
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
            'part_name' => 'required|min:2|max:200',
            'part_firstName' => 'required|min:2|max:250',
            'part_cin' => 'required|min:12|max:12|unique:users,cin',
            'customer_email' => 'required|unique:users,email',
            'password' => 'required|min:8'
        ];
    }

    public function messages(): array
    {
        return [
            'part_name.required' => 'Ce champs est obligatoire',
            'part_firstName.required' => 'Ce champs est obligatoire',
            'part_cin.required' => 'Ce champs est obligatoire',
            'customer_email.required' => 'Ce champs est obligatoire',
            'password.required' => 'Ce champs est obligatoire'
        ];
    }
}

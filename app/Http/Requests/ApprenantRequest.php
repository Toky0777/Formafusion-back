<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;use Illuminate\Http\Exceptions\HttpResponseException;

class ApprenantRequest extends FormRequest
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
            'idEntreprise' => 'required|exists:customers,idCustomer',
            'emp_name' => 'required|min:2|max:200'
        ];
    }

    public function messages(): array
    {
        return [
            'idEntreprise.required' => "Ce champs est obligatoire !",
            'emp_name.required' => "Ce champs est obligatoire !"
        ];
    }
}

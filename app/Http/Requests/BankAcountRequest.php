<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankAcountRequest extends FormRequest
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
            'ba_account_number' => 'required|unique:bankacounts,ba_account_number',
            'ba_name' => 'required',
            'ba_idPostal' => 'required',
            'ba_quartier' => 'required',
            'ba_titulaire' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'ba_idCustomer.required' => 'Id customer est requis.',
            'ba_titulaire.required' => 'Le titulaire du compte est requis.',
            'ba_name.required' => 'Le nom de votre banque est requis.',
            'ba_quartier.required' => 'Le quartier de votre banque est requis.',
            'ba_idPostal.required' => 'Le code postal de votre banque est requis.',
            'ba_account_number.required' => 'Le numéro de compte est requis.',
            'ba_account_number.unique' => 'Ce numéro de compte existe déjà.',
        ];
    }
}

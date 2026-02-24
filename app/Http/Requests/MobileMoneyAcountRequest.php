<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileMoneyAcountRequest extends FormRequest
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
            'mm_phone' => 'required|unique:mobilemoneyacounts,mm_phone',
            'mm_operateur' => 'required',
            'mm_titulaire' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'mm_titulaire.required' => 'Le titulaire du compte est requis.',
            'mm_operateur.required' => 'Le nom de l operateur est requis.',
            'mm_phone.required' => 'Le numéro de compte est requis.',
            'mm_phone.unique' => 'Ce numéro téléphone de compte existe déjà.',
        ];
    }
}

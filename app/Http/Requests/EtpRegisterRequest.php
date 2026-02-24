<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EtpRegisterRequest extends FormRequest
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
            'customer_name' => 'required|min:2|max:200',
            'customer_nif'  => 'required|unique:entreprises,nif',
            'referent_name' => 'required|min:2|max:250',
            'referent_firstName' => 'required|min:2|max:250',
            'customer_email' => 'required|unique:users,email',
            'password' => 'required|min:8'
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => "Ce champs est obligatoire",
            'customer_name.min' => "Veuillez mettre au moins 2 Caractères",
            'customer_name.max' => "Veuillez ne pas dépasser les 50 Caractères",
            'referent_name.required' => "Ce champs est obligatoire",
            'referent_name.min' => "Veuillez mettre au moins 2 Caractères",
            'referent_firstName.required' => "Ce champs est obligatoire",
            'password.required' => "Ce champs est obligatoire",
            'password.min' => "Veuillez mettre au moins 8 Caractères"
        ];
    }
}

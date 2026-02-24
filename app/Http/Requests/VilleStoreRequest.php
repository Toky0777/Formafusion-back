<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VilleStoreRequest extends FormRequest
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
            'ville_name' => 'required|min:3|max:150',
            'vi_code_postal' => 'required',
            'idVille' => 'required|exists:villes,idVille'
        ];
    }

    public function messages(): array
    {
        return[
            'ville_name.required' => "Ce champs est obligatoire",
            'ville_name.min' => "Vuillez au moin mettre 3 caractères",
            'ville_name.max' => "Vuillez ne pas dépasser 150 caractères",
            'vi_code_postal.required' => "Ce champs est obligatoire"
        ];
    }
}

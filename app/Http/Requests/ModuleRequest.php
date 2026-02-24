<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModuleRequest extends FormRequest
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
            'module_reference' => 'nullable|string|max:255',
            'name' => 'required|string|min:2|max:200',
            'subtitle' => 'nullable|string|min:2|max:200',
            'module_dureeH' => 'nullable|numeric|min:0',
            'module_dureeJ' => 'nullable|numeric|min:0',
            'module_min_appr' => 'nullable|integer|min:0',
            'module_max_appr' => 'nullable|integer|min:0',
            'module_price' => 'nullable|numeric|min:0',
            'module_prix_groupe' => 'nullable|numeric|min:0',
            'id_domaine_formation' => 'required',
            'idLevel' => 'required',
        ];
    }


  
}

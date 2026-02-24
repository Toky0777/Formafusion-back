<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
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
            'title' => 'required|min:2|max:150',
            'reference' => 'nullable|min:2|max:150',
            'description' => 'nullable|min:2|max:150',
            'idTypeProjet' => 'required|exists:type_projets,idTypeProjet',
            'idModalite' => 'nullable|exists:modalites,idModalite'
        ];
    }
}

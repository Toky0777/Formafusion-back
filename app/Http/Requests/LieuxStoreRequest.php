<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LieuxStoreRequest extends FormRequest
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
            'li_name' => 'required|min:3|max:150',
            'idVille' => 'required|exists:villes,idVille',
            'idLieuType' => 'required|exists:lieu_types,idLieuType',
            'idVilleCoded' => 'required|exists:ville_codeds,id',
        ];
    }
}

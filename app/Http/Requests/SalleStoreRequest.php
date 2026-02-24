<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalleStoreRequest extends FormRequest
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
            'salle_name' => 'required|min:2|max:150',
            'idLieu' => 'required|exists:lieux,idLieu',
            'salle_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ];
    }

    public function messages(): array
    {
        return[
            'salle_name.required' => 'Ce champs est obligatoire',
            'idLieu.required' => 'Ce champs est obligatoire'
        ];
    }
}

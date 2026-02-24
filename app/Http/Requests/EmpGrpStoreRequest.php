<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpGrpStoreRequest extends FormRequest
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
            'emp_matricule' => 'required|min:2|max:200|unique:users,matricule',
            'emp_name' => 'required|min:2|max:200',
            'emp_firstname' => 'nullable|min:2|max:200',
            'emp_email' => 'required|min:2|max:200|unique:users,email',
            'emp_phone' => 'nullable|min:2|max:200'
        ];
    }

    public function messages(): array
    {
        return [
            'emp_matricule.required' => 'Ce champs est obligatoire',
            'emp_name.required' => 'Ce champs est obligatoire',
            'emp_email.required' => 'Ce champs est obligatoire'
        ];
    }
}

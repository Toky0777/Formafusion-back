<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchLearnerRequest extends FormRequest
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
            'employe_id' => 'required|integer|exists:employes,idEmploye',
            'batch_id' => 'required|integer|exists:batches,id'
        ];
    }

    public function messages()
    {
        return [
            'employe_id.required' => 'The employe is required',
            'employe_id.integer' => 'The employe must be integer',
            'batch_id.required' => 'The batch is required',
            'batch_id.integer' => 'The batch must be integer'
        ];
    }
}

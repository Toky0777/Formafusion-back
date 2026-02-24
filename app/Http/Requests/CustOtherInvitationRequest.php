<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustOtherInvitationRequest extends FormRequest
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
            'cust_other_cin'           => 'required|min:12|max:12',
            'etp_name'                 => 'required|min:2',
            'cust_other_email'         => 'required|email'
        ];
    }

    public function messages(): array
    {
        return [
            'cust_other_cin.required' => 'Ce champs est obligatoire',
            'cust_other_cin.min' => 'Veuillez mettre 12 chiffres au minimum',
            'cust_other_cin.max' => 'Veuillez mettre 12 chiffres au maximum',
            'etp_name.required' => 'Ce champs est obligatoire',
            'cust_other_email.required' => 'Ce champs est obligatoire'
        ];
    }
}

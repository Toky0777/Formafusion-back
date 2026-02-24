<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserOtherRegisterRequest extends FormRequest
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
            'user_other_name' => 'required|min:2|max:200',
            'user_name'=> 'required|min:2|max:200',
            'user_other_cin' => 'required|unique:users,cin|min:12|max:12',
            'customer_email' => 'required|unique:users,email'
        ];
    }
}

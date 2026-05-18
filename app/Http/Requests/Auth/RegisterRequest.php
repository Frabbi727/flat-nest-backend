<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
            'phone'    => 'required|regex:/^01[3-9]\d{8}$/|unique:users',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'An account with this email already exists. Please log in, or use Google Sign-In if you registered with Google.',
            'phone.unique' => 'This phone number is already registered to another account.',
        ];
    }
}

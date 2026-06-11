<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterBasicRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'phone'    => ['required', 'string', Rule::unique('users', 'phone')->ignore($this->user()->id)],
            'password' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required'  => 'Phone number is required.',
            'phone.unique'    => 'This phone number is already registered with another account.',
            'password.required' => 'Password is required.',
            'password.min'    => 'Password must be at least 8 characters.',
        ];
    }
}

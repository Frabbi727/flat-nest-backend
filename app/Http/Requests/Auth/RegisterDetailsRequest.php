<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterDetailsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'role'          => 'required|in:renter,owner',
            'date_of_birth' => 'required|date|before:-18 years',
        ];
    }
}

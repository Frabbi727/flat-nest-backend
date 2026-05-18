<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class RegisterDetailsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $validRoles = implode(',', array_column(UserRole::cases(), 'value'));

        return [
            'role' => "required|in:{$validRoles}",
        ];
    }
}

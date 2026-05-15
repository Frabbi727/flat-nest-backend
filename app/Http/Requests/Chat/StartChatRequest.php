<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class StartChatRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'listing_id'      => 'required|uuid|exists:listings,id',
            'initial_message' => 'required|string|max:1000',
        ];
    }
}

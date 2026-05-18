<?php

namespace App\Http\Requests\Device;

use Illuminate\Foundation\Http\FormRequest;

class RegisterFcmTokenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'fcm_token'    => 'required|string',
            'device_type'  => 'nullable|in:android,ios,web',
            'device_model' => 'nullable|string|max:100',
        ];
    }
}

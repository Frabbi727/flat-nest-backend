<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:Family,Bachelor,Student,Couple,Sublet',
            'price'       => 'required|integer|min:1',
            'beds'        => 'required|integer|min:0',
            'baths'       => 'required|integer|min:0',
            'deposit'     => 'nullable|integer|min:0',
            'size'        => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'amenities'   => 'nullable|array',
            'amenities.*' => 'string',
        ];
    }
}

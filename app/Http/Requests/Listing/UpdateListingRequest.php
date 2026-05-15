<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => 'sometimes|string|max:255',
            'area'        => 'sometimes|string',
            'type'        => 'sometimes|in:Family,Bachelor,Student,Couple,Sublet',
            'price'       => 'sometimes|integer|min:1',
            'beds'        => 'sometimes|integer|min:0',
            'baths'       => 'sometimes|integer|min:0',
            'deposit'     => 'nullable|integer|min:0',
            'size'        => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'amenities'   => 'nullable|array',
            'amenities.*' => 'integer|exists:amenities,id',
        ];
    }
}

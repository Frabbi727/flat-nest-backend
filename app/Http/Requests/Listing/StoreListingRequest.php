<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'           => 'required|string|max:255',
            'listing_type_id' => 'required|integer|exists:listing_types,id',
            'price'           => 'required|integer|min:0',
            'beds'            => 'required|integer|min:0',
            'baths'           => 'required|integer|min:0',
            'deposit'         => 'nullable|integer|min:0',
            'size'            => 'nullable|integer|min:0',
            'description'     => 'nullable|string',
            'available_from'  => 'nullable|date|after_or_equal:today',
            'floor_no'        => 'nullable|integer|min:0|max:100',
            'facing_id'       => 'nullable|integer|exists:listing_facings,id',
            'amenities'       => 'nullable|array',
            'amenities.*'     => 'integer|exists:amenities,id',
        ];
    }
}

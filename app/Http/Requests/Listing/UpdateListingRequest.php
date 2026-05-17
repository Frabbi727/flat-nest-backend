<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'             => 'nullable|string|max:255',
            'listing_type_id'   => 'nullable|integer|exists:listing_types,id',
            'price'             => 'nullable|integer|min:0',
            'deposit'           => 'nullable|integer|min:0',
            'beds'              => 'nullable|integer|min:0',
            'baths'             => 'nullable|integer|min:0',
            'size'              => 'nullable|integer|min:0',
            'description'       => 'nullable|string',
            'available_from'    => 'nullable|date|after_or_equal:today',
            'floor_no'          => 'nullable|integer|min:0|max:100',
            'facing_id'         => 'nullable|integer|exists:listing_facings,id',
            'division_id'       => 'nullable|exists:divisions,id',
            'district_id'       => 'nullable|exists:districts,id',
            'upazila_id'        => 'nullable|exists:upazilas,id',
            'union_id'          => 'nullable|exists:unions,id',
            'area'              => 'nullable|string|max:255',
            'coord_x'           => 'nullable|numeric',
            'coord_y'           => 'nullable|numeric',
            'road'              => 'nullable|string|max:255',
            'house_name'        => 'nullable|string|max:255',
            'block'             => 'nullable|string|max:100',
            'section'           => 'nullable|string|max:100',
            'owner_name'        => 'nullable|string|max:255',
            'owner_phone'       => 'nullable|string|max:20',
            'owner_alt_phone'   => 'nullable|string|max:20',
            'owner_email'       => 'nullable|email|max:255',
            'preferred_contact' => 'nullable|in:call,whatsapp,both',
            'amenities'         => 'nullable|array',
            'amenities.*'       => 'integer|exists:amenities,id',
        ];
    }
}

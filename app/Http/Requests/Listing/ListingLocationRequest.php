<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class ListingLocationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'area'        => 'nullable|string|max:255',
            'division_id' => 'nullable|integer|exists:divisions,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'upazila_id'  => 'nullable|integer|exists:upazilas,id',
            'union_id'    => 'nullable|integer|exists:unions,id',
            'coord_x'     => 'nullable|numeric',
            'coord_y'     => 'nullable|numeric',
            'road'        => 'nullable|string|max:255',
            'house_name'  => 'nullable|string|max:255',
            'block'       => 'nullable|string|max:100',
            'section'     => 'nullable|string|max:100',
        ];
    }
}
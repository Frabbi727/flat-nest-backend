<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class ListingLocationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'area'           => 'required|string',
            'division_id'    => 'nullable|integer|exists:divisions,id',
            'district_id'    => 'nullable|integer|exists:districts,id',
            'upazila_id'     => 'nullable|integer|exists:upazilas,id',
            'union_id'       => 'nullable|integer|exists:unions,id',
            'road_and_house' => 'nullable|string',
            'coord_x'        => 'nullable|numeric',
            'coord_y'        => 'nullable|numeric',
        ];
    }
}
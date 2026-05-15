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
            'road_and_house' => 'nullable|string',
            'coord_x'        => 'nullable|numeric',
            'coord_y'        => 'nullable|numeric',
        ];
    }
}
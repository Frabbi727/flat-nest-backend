<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Division;
use App\Models\Union;
use App\Models\Upazila;
use Illuminate\Http\JsonResponse;

class GeoController extends Controller
{
    public function divisions(): JsonResponse
    {
        return response()->json(
            Division::orderBy('name')->get(['id', 'name', 'bn_name'])
        );
    }

    public function districts(int $divisionId): JsonResponse
    {
        return response()->json(
            District::where('division_id', $divisionId)
                ->orderBy('name')
                ->get(['id', 'division_id', 'name', 'bn_name'])
        );
    }

    public function upazilas(int $districtId): JsonResponse
    {
        return response()->json(
            Upazila::where('district_id', $districtId)
                ->orderBy('name')
                ->get(['id', 'district_id', 'name', 'bn_name'])
        );
    }

    public function unions(int $upazilaId): JsonResponse
    {
        return response()->json(
            Union::where('upazilla_id', $upazilaId)
                ->orderBy('name')
                ->get(['id', 'upazilla_id', 'name', 'bn_name'])
        );
    }
}

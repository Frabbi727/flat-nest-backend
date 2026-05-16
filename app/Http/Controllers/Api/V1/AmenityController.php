<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\Amenity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(Amenity::orderBy('label')->get(['id', 'name', 'label']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|unique:amenities,name',
            'label' => 'required|string',
        ]);

        return ApiResponse::success(Amenity::create($data), null, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $amenity = Amenity::findOrFail($id);

        $data = $request->validate([
            'name'  => 'sometimes|string|unique:amenities,name,' . $id,
            'label' => 'sometimes|string',
        ]);

        $amenity->update($data);

        return ApiResponse::success($amenity);
    }

    public function destroy(int $id): JsonResponse
    {
        Amenity::findOrFail($id)->delete();

        return ApiResponse::success(null, 'Amenity deleted');
    }
}

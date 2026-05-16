<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Models\ListingType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(ListingType::orderBy('label')->get(['id', 'name', 'label']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|unique:listing_types,name',
            'label' => 'required|string',
        ]);

        return ApiResponse::success(ListingType::create($data), null, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $type = ListingType::findOrFail($id);

        $data = $request->validate([
            'name'  => 'sometimes|string|unique:listing_types,name,' . $id,
            'label' => 'sometimes|string',
        ]);

        $type->update($data);

        return ApiResponse::success($type);
    }

    public function destroy(int $id): JsonResponse
    {
        ListingType::findOrFail($id)->delete();

        return ApiResponse::success(null, 'Type deleted');
    }
}

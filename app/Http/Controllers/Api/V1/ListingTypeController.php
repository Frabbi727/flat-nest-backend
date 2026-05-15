<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ListingType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ListingType::orderBy('label')->get(['id', 'name', 'label']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|unique:listing_types,name',
            'label' => 'required|string',
        ]);

        return response()->json(ListingType::create($data), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $type = ListingType::findOrFail($id);

        $data = $request->validate([
            'name'  => 'sometimes|string|unique:listing_types,name,' . $id,
            'label' => 'sometimes|string',
        ]);

        $type->update($data);

        return response()->json($type);
    }

    public function destroy(int $id): JsonResponse
    {
        ListingType::findOrFail($id)->delete();

        return response()->json(['message' => 'Type deleted']);
    }
}

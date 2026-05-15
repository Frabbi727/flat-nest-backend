<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type      = $request->query('type');
        $maxPrice  = $request->query('maxPrice');
        $amenities = $request->query('amenities');

        $listings = Listing::with(['owner:id,name,phone', 'photos'])
            ->where('status', 'active')
            ->when($type,     fn ($q) => $q->where('type', $type))
            ->when($maxPrice, fn ($q) => $q->where('price', '<=', $maxPrice))
            ->when($amenities, function ($q) use ($amenities) {
                $list = explode(',', $amenities);
                foreach ($list as $amenity) {
                    $q->whereJsonContains('amenities', trim($amenity));
                }
            })
            ->latest()
            ->get();

        return response()->json($listings);
    }

    public function show(string $id): JsonResponse
    {
        $listing = Listing::with(['owner:id,name,phone', 'photos'])->find($id);

        if (! $listing) {
            return response()->json(['message' => 'Listing not found', 'code' => 'NOT_FOUND'], 404);
        }

        $listing->increment('views');

        return response()->json($listing->fresh(['owner:id,name,phone', 'photos']));
    }
}
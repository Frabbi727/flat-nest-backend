<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $savedIds = $request->user()->wishlist()->pluck('listings.id');

        return response()->json(['saved_ids' => $savedIds]);
    }

    public function save(Request $request, string $listingId): JsonResponse
    {
        $listing = Listing::find($listingId);

        if (! $listing) {
            return response()->json(['message' => 'Listing not found', 'code' => 'NOT_FOUND'], 404);
        }

        $request->user()->wishlist()->syncWithoutDetaching([$listingId]);

        return response()->json(['message' => 'Saved']);
    }

    public function remove(Request $request, string $listingId): JsonResponse
    {
        $request->user()->wishlist()->detach($listingId);

        return response()->json(['message' => 'Removed']);
    }
}
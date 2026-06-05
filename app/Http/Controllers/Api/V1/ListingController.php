<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\ListingResource;
use App\Services\ListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function __construct(private readonly ListingService $listings) {}

    public function index(Request $request): JsonResponse
    {
        $filterParams = [
            'search', 'listing_type_id', 'price_min', 'price_max', 'beds', 'baths',
            'facing_id', 'floor_min', 'floor_max', 'size_min', 'size_max',
            'available_from_start', 'available_from_end', 'amenities',
            'division_id', 'district_id', 'upazila_id', 'union_id', 'sort_by',
        ];

        if ($request->hasAny($filterParams) && ! auth('sanctum')->check()) {
            return ApiResponse::error('Unauthenticated.', 'UNAUTHENTICATED', 401);
        }

        $filters = $request->only([
            'search',
            'listing_type_id',
            'price_min',
            'price_max',
            'beds',
            'baths',
            'facing_id',
            'floor_min',
            'floor_max',
            'size_min',
            'size_max',
            'available_from_start',
            'available_from_end',
            'division_id',
            'district_id',
            'upazila_id',
            'union_id',
            'amenities',
            'sort_by',
        ]);

        $paginator = $this->listings->getFeed($filters);
        return ApiResponse::paginated(ListingResource::collection($paginator), $paginator);
    }

    public function show(string $id): JsonResponse
    {
        return ApiResponse::success(new ListingResource($this->listings->getById($id)));
    }

    /**
     * GET /api/v1/listings/nearby
     *
     * Required: coord_x (longitude), coord_y (latitude)
     * Optional: radius (0.5–50 km, default 10)
     *           listing_type_id, price_min, price_max
     *           beds, baths
     *           facing_id, floor_min, floor_max, size_min, size_max
     *           available_from_start, available_from_end
     *           division_id, district_id, upazila_id, union_id
     *           search, amenities
     *           sort_by (price_asc|price_desc|available_soon — secondary, after distance)
     */
    public function nearby(Request $request): JsonResponse
    {
        // Both coordinates must be present together
        $hasCx = $request->filled('coord_x');
        $hasCy = $request->filled('coord_y');

        if ($hasCx !== $hasCy) {
            return ApiResponse::error(
                'Both coord_x (longitude) and coord_y (latitude) are required for geo search.',
                'GEO_INCOMPLETE',
                422
            );
        }

        if (! $hasCx && ! $hasCy) {
            return ApiResponse::error(
                'coord_x and coord_y are required.',
                'GEO_REQUIRED',
                422
            );
        }

        $params = $request->only([
            // Geo (required)
            'coord_x', 'coord_y', 'radius',
            // Listing type & price
            'listing_type_id', 'price_min', 'price_max',
            // Rooms
            'beds', 'baths',
            // Property details
            'facing_id', 'floor_min', 'floor_max', 'size_min', 'size_max',
            // Availability
            'available_from_start', 'available_from_end',
            // Location (narrow down within the radius)
            'division_id', 'district_id', 'upazila_id', 'union_id',
            // Text search
            'search',
            // Amenities (comma-separated IDs e.g. "1,3,5")
            'amenities',
            // Secondary sort — distance_km ASC is always primary
            // Allowed values: price_asc | price_desc | available_soon
            'sort_by',
        ]);

        $paginator = $this->listings->getNearby($params);

        if ($paginator->isEmpty()) {
            return ApiResponse::success([], 'No listings found within the specified area.');
        }

        return ApiResponse::paginated(ListingResource::collection($paginator), $paginator);
    }
}

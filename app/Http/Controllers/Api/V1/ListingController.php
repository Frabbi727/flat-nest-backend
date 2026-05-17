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
}

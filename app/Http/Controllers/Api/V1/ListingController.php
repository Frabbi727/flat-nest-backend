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
            'division_id',
            'district_id',
            'upazila_id',
            'union_id',
            'listing_type_id',
            'minPrice',
            'maxPrice',
            'beds',
            'amenities',
        ]);

        $paginator = $this->listings->getFeed($filters);
        return ApiResponse::paginated(ListingResource::collection($paginator), $paginator);
    }

    public function show(string $id): JsonResponse
    {
        return ApiResponse::success(new ListingResource($this->listings->getById($id)));
    }
}

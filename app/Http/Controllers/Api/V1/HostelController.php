<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\HostelDetailResource;
use App\Http\Resources\HostelResource;
use App\Services\HostelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostelController extends Controller
{
    public function __construct(private readonly HostelService $hostels) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'type_id',
            'gender_policy',
            'price_min',
            'price_max',
            'price_unit',
            'division_id',
            'district_id',
            'upazila_id',
            'amenities',
            'min_vacant_seats',
            'is_verified',
            'sort_by',
        ]);

        $paginator = $this->hostels->getFeed($filters);
        return ApiResponse::paginated(HostelResource::collection($paginator), $paginator);
    }

    public function show(string $id): JsonResponse
    {
        return ApiResponse::success(new HostelDetailResource($this->hostels->getById($id)));
    }

    public function review(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = $this->hostels->addReview($id, $request->user()->id, $data);

        return ApiResponse::success([
            'id'         => $review->id,
            'rating'     => $review->rating,
            'comment'    => $review->comment,
            'created_at' => $review->created_at,
            'user'       => $review->user ? ['id' => $review->user->id, 'name' => $review->user->name] : null,
        ], 'Review submitted.', 201);
    }
}

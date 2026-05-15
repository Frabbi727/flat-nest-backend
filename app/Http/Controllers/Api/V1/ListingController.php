<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Services\ListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListingController extends Controller
{
    public function __construct(private readonly ListingService $listings) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ListingResource::collection(
            $this->listings->getFeed($request->only(['listing_type_id', 'maxPrice', 'amenities']))
        );
    }

    public function show(string $id): JsonResponse|ListingResource
    {
        try {
            return new ListingResource($this->listings->getById($id));
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        }
    }
}

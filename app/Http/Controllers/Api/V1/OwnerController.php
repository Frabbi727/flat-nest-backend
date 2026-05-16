<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Listing\ListingLocationRequest;
use App\Http\Requests\Listing\StoreListingRequest;
use App\Http\Requests\Listing\UpdateListingRequest;
use App\Http\Requests\Listing\UploadListingPhotosRequest;
use App\Http\Resources\ListingResource;
use App\Services\ListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerController extends Controller
{
    public function __construct(private readonly ListingService $listings) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            ListingResource::collection($this->listings->getOwnerDashboard($request->user()->id))
        );
    }

    public function store(StoreListingRequest $request): JsonResponse
    {
        $listing = $this->listings->create($request->user()->id, $request->validated());

        return ApiResponse::success(
            ['id' => $listing->id, 'listing_step' => 2],
            'Listing draft created. Upload photos to submit for review.',
            201
        );
    }

    public function uploadPhotos(UploadListingPhotosRequest $request, string $id): JsonResponse
    {
        $this->listings->addPhotos($id, $request->user()->id, $request->file('photos'));
        return ApiResponse::success(['listing_step' => 3]);
    }

    public function updateLocation(ListingLocationRequest $request, string $id): JsonResponse
    {
        $this->listings->updateLocation($id, $request->user()->id, $request->validated());
        return ApiResponse::success(['listing_step' => 4]);
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        $listing = $this->listings->submit($id, $request->user()->id);
        return ApiResponse::success(
            new ListingResource($listing->fresh(['owner:id,name,phone', 'photos', 'amenities'])),
            'Listing submitted for review.'
        );
    }

    public function update(UpdateListingRequest $request, string $id): JsonResponse
    {
        return ApiResponse::success(
            new ListingResource($this->listings->update($id, $request->user()->id, $request->validated()))
        );
    }

    public function markAsRented(Request $request, string $id): JsonResponse
    {
        $this->listings->markAsRented($id, $request->user()->id);
        return ApiResponse::success(null, 'Listing marked as rented.');
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->listings->delete($id, $request->user()->id);
        return ApiResponse::success(null, 'Listing deleted');
    }
}

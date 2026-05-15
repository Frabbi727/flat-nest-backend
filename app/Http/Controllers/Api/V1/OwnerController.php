<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Listing\ListingLocationRequest;
use App\Http\Requests\Listing\StoreListingRequest;
use App\Http\Requests\Listing\UpdateListingRequest;
use App\Http\Requests\Listing\UploadListingPhotosRequest;
use App\Http\Resources\ListingResource;
use App\Services\ListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OwnerController extends Controller
{
    public function __construct(private readonly ListingService $listings) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ListingResource::collection(
            $this->listings->getOwnerDashboard($request->user()->id)
        );
    }

    public function store(StoreListingRequest $request): JsonResponse
    {
        $listing = $this->listings->create($request->user()->id, $request->validated());

        return response()->json([
            'id'           => $listing->id,
            'message'      => 'Listing draft created. Upload photos to submit for review.',
            'listing_step' => 2,
        ], 201);
    }

    public function uploadPhotos(UploadListingPhotosRequest $request, string $id): JsonResponse
    {
        try {
            $this->listings->addPhotos($id, $request->user()->id, $request->file('photos'));
            return response()->json(['listing_step' => 3]);
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'FORBIDDEN'], 403);
        }
    }

    public function updateLocation(ListingLocationRequest $request, string $id): JsonResponse
    {
        try {
            $this->listings->updateLocation($id, $request->user()->id, $request->validated());
            return response()->json(['listing_step' => 4]);
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        }
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        try {
            $listing = $this->listings->submit($id, $request->user()->id);
            return response()->json([
                'message' => 'Listing submitted for review.',
                'listing' => new ListingResource($listing->fresh(['owner:id,name,phone', 'photos'])),
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        } catch (\Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'INCOMPLETE'], 422);
        }
    }

    public function update(UpdateListingRequest $request, string $id): JsonResponse|ListingResource
    {
        try {
            return new ListingResource(
                $this->listings->update($id, $request->user()->id, $request->validated())
            );
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->listings->delete($id, $request->user()->id);
            return response()->json(['message' => 'Listing deleted']);
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        }
    }
}

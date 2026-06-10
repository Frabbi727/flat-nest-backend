<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\ListingAccessRequestResource;
use App\Models\Listing;
use App\Services\ListingAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListingAccessController extends Controller
{
    public function __construct(private readonly ListingAccessService $accessService) {}

    public function request(Request $request, string $listingId): JsonResponse
    {
        $listing = Listing::find($listingId);

        if (! $listing) {
            throw new NotFoundHttpException('Listing not found.');
        }

        $accessRequest = $this->accessService->requestAccess($listing, $request->user());

        return ApiResponse::success(
            ['id' => $accessRequest->id, 'status' => $accessRequest->status->value],
            'Access request sent to the owner.',
            201
        );
    }

    public function ownerIndex(Request $request): JsonResponse
    {
        $status   = $request->query('status');
        $requests = $this->accessService->getOwnerRequests($request->user()->id, $status);

        return ApiResponse::paginated(
            ListingAccessRequestResource::collection($requests),
            $requests
        );
    }

    public function accept(Request $request, string $id): JsonResponse
    {
        $this->accessService->grantAccess($id, $request->user());

        return ApiResponse::success(['id' => $id, 'status' => 'accepted'], 'Access granted.');
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $this->accessService->denyAccess($id, $request->user());

        return ApiResponse::success(['id' => $id, 'status' => 'rejected'], 'Access request declined.');
    }
}

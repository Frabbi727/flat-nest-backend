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
        $filters   = $request->only(['status', 'type_id']);
        $paginator = $this->listings->getOwnerDashboard($request->user()->id, $filters);

        return ApiResponse::paginated(ListingResource::collection($paginator), $paginator);
    }

    public function store(StoreListingRequest $request): JsonResponse
    {
        $listing = $this->listings->create($request->user()->id, $request->validated());

        return ApiResponse::success(new ListingResource($listing), 'Listing draft created.', 201);
    }

    public function uploadPhotos(UploadListingPhotosRequest $request, string $id): JsonResponse
    {
        $this->listings->addPhotos($id, $request->user()->id, $request->file('photos'));
        return ApiResponse::success(['listing_step' => 3]);
    }

    public function updateLocation(ListingLocationRequest $request, string $id): JsonResponse
    {
        $listing = $this->listings->updateLocation($id, $request->user()->id, $request->validated());

        return ApiResponse::success(new ListingResource($listing));
    }

    public function updateOwnerInfo(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'owner_name'        => 'nullable|string|max:255',
            'owner_phone'       => 'nullable|string|max:20',
            'owner_alt_phone'   => 'nullable|string|max:20',
            'owner_email'       => 'nullable|email|max:255',
            'preferred_contact' => 'nullable|in:call,whatsapp,both',
        ]);

        $user = $request->user();
        $data['owner_name']  = $data['owner_name']  ?? $user->name;
        $data['owner_phone'] = $data['owner_phone'] ?? $user->phone;
        $data['owner_email'] = $data['owner_email'] ?? $user->email;

        $listing = $this->listings->updateOwnerInfo($id, $user->id, $data);

        return ApiResponse::success(new ListingResource($listing));
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

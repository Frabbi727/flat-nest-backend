<?php

namespace App\Repositories;

use App\Contracts\Repositories\ListingAccessRequestRepositoryInterface;
use App\Enums\ListingAccessStatus;
use App\Models\ListingAccessRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class ListingAccessRequestRepository implements ListingAccessRequestRepositoryInterface
{
    public function findByListingAndRequester(string $listingId, string $requesterId): ?ListingAccessRequest
    {
        return ListingAccessRequest::where('listing_id', $listingId)
            ->where('requester_id', $requesterId)
            ->first();
    }

    public function upsert(string $listingId, string $requesterId, string $ownerId): ListingAccessRequest
    {
        return ListingAccessRequest::updateOrCreate(
            ['listing_id' => $listingId, 'requester_id' => $requesterId],
            ['owner_id' => $ownerId, 'status' => ListingAccessStatus::Pending],
        );
    }

    public function hasAccepted(string $listingId, string $requesterId): bool
    {
        return ListingAccessRequest::where('listing_id', $listingId)
            ->where('requester_id', $requesterId)
            ->where('status', ListingAccessStatus::Accepted)
            ->exists();
    }

    public function getStatusForUser(string $listingId, string $requesterId): ?ListingAccessStatus
    {
        return ListingAccessRequest::where('listing_id', $listingId)
            ->where('requester_id', $requesterId)
            ->value('status');
    }

    public function findForOwner(string $ownerId, ?string $status, int $perPage): LengthAwarePaginator
    {
        return ListingAccessRequest::with([
            'listing:id,title',
            'requester:id,name,avatar_url',
        ])
            ->where('owner_id', $ownerId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function findById(string $id): ?ListingAccessRequest
    {
        return ListingAccessRequest::with(['listing:id,title,owner_id', 'requester:id,name,avatar_url'])->find($id);
    }

    public function updateStatus(string $id, ListingAccessStatus $status): void
    {
        ListingAccessRequest::where('id', $id)->update(['status' => $status]);
    }
}

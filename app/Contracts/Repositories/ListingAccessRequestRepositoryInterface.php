<?php

namespace App\Contracts\Repositories;

use App\Enums\ListingAccessStatus;
use App\Models\ListingAccessRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface ListingAccessRequestRepositoryInterface
{
    public function findByListingAndRequester(string $listingId, string $requesterId): ?ListingAccessRequest;

    public function upsert(string $listingId, string $requesterId, string $ownerId): ListingAccessRequest;

    public function hasAccepted(string $listingId, string $requesterId): bool;

    public function getStatusForUser(string $listingId, string $requesterId): ?ListingAccessStatus;

    public function findForOwner(string $ownerId, ?string $status, int $perPage): LengthAwarePaginator;

    public function findById(string $id): ?ListingAccessRequest;

    public function updateStatus(string $id, ListingAccessStatus $status): void;
}

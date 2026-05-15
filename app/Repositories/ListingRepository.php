<?php

namespace App\Repositories;

use App\Contracts\Repositories\ListingRepositoryInterface;
use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;

class ListingRepository implements ListingRepositoryInterface
{
    public function findActive(array $filters): Collection
    {
        return Listing::with(['owner:id,name,phone', 'photos', 'amenities', 'listingType'])
            ->where('status', ListingStatus::Active)
            ->when($filters['listing_type_id'] ?? null, fn ($q, $v) => $q->where('listing_type_id', $v))
            ->when($filters['maxPrice'] ?? null, fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($filters['amenities'] ?? null, function ($q, $amenities) {
                foreach (explode(',', $amenities) as $id) {
                    $q->whereHas('amenities', fn ($aq) => $aq->where('amenities.id', (int) trim($id)));
                }
            })
            ->latest()
            ->get();
    }

    public function findById(string $id): ?Listing
    {
        return Listing::with(['owner:id,name,phone', 'photos', 'amenities', 'listingType'])->find($id);
    }

    public function findByOwner(string $ownerId): Collection
    {
        return Listing::with(['photos', 'amenities', 'listingType'])
            ->withCount('chats as inquiries')
            ->where('owner_id', $ownerId)
            ->latest()
            ->get();
    }

    public function create(array $data): Listing
    {
        return Listing::create($data);
    }

    public function update(Listing $listing, array $data): Listing
    {
        $listing->update($data);
        return $listing->fresh(['photos', 'amenities']);
    }

    public function delete(Listing $listing): void
    {
        $listing->delete();
    }

    public function incrementViews(Listing $listing): void
    {
        $listing->increment('views');
    }
}

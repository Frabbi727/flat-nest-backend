<?php

namespace App\Repositories;

use App\Contracts\Repositories\ListingRepositoryInterface;
use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ListingRepository implements ListingRepositoryInterface
{
    public function findActive(array $filters): LengthAwarePaginator
    {
        return Listing::with(['owner:id,name,phone', 'photos', 'amenities', 'listingType'])
            ->where('status', ListingStatus::Active)
            // keyword search across text fields
            ->when($filters['search'] ?? null, function ($q, $search) {
                $term = '%' . $search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('title', 'like', $term)
                      ->orWhere('area', 'like', $term)
                      ->orWhere('road_and_house', 'like', $term)
                      ->orWhere('description', 'like', $term);
                });
            })
            // geo filters
            ->when($filters['division_id'] ?? null, fn ($q, $v) => $q->where('division_id', $v))
            ->when($filters['district_id'] ?? null, fn ($q, $v) => $q->where('district_id', $v))
            ->when($filters['upazila_id'] ?? null, fn ($q, $v) => $q->where('upazila_id', $v))
            ->when($filters['union_id'] ?? null, fn ($q, $v) => $q->where('union_id', $v))
            // listing type
            ->when($filters['listing_type_id'] ?? null, fn ($q, $v) => $q->where('listing_type_id', $v))
            // price range
            ->when($filters['minPrice'] ?? null, fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($filters['maxPrice'] ?? null, fn ($q, $v) => $q->where('price', '<=', $v))
            // minimum beds
            ->when($filters['beds'] ?? null, fn ($q, $v) => $q->where('beds', '>=', $v))
            // amenities (all must match — comma-separated IDs)
            ->when($filters['amenities'] ?? null, function ($q, $amenities) {
                foreach (explode(',', $amenities) as $id) {
                    $q->whereHas('amenities', fn ($aq) => $aq->where('amenities.id', (int) trim($id)));
                }
            })
            ->latest()
            ->paginate(15);
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

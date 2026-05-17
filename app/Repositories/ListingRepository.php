<?php

namespace App\Repositories;

use App\Contracts\Repositories\ListingRepositoryInterface;
use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Pagination\LengthAwarePaginator;

class ListingRepository implements ListingRepositoryInterface
{
    public function findActive(array $filters): LengthAwarePaginator
    {
        $query = Listing::with(['owner:id,name,phone', 'photos', 'amenities', 'listingType', 'facing', 'division', 'district', 'upazila', 'union'])
            ->where('status', ListingStatus::Active)
            ->when($filters['search'] ?? null, function ($q, $search) {
                $term = '%' . $search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('title', 'like', $term)
                      ->orWhere('area', 'like', $term)
                      ->orWhere('road', 'like', $term)
                      ->orWhere('house_name', 'like', $term)
                      ->orWhere('block', 'like', $term)
                      ->orWhere('section', 'like', $term);
                });
            })
            ->when($filters['division_id'] ?? null, fn ($q, $v) => $q->where('division_id', $v))
            ->when($filters['district_id'] ?? null, fn ($q, $v) => $q->where('district_id', $v))
            ->when($filters['upazila_id'] ?? null, fn ($q, $v) => $q->where('upazila_id', $v))
            ->when($filters['union_id'] ?? null, fn ($q, $v) => $q->where('union_id', $v))
            ->when($filters['listing_type_id'] ?? null, fn ($q, $v) => $q->where('listing_type_id', $v))
            ->when($filters['price_min'] ?? null, fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($filters['price_max'] ?? null, fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($filters['beds'] ?? null, fn ($q, $v) => $q->where('beds', $v))
            ->when($filters['baths'] ?? null, fn ($q, $v) => $q->where('baths', $v))
            ->when($filters['facing_id'] ?? null, fn ($q, $v) => $q->where('facing_id', $v))
            ->when($filters['floor_min'] ?? null, fn ($q, $v) => $q->where('floor_no', '>=', $v))
            ->when($filters['floor_max'] ?? null, fn ($q, $v) => $q->where('floor_no', '<=', $v))
            ->when($filters['size_min'] ?? null, fn ($q, $v) => $q->where('size', '>=', $v))
            ->when($filters['size_max'] ?? null, fn ($q, $v) => $q->where('size', '<=', $v))
            ->when($filters['available_from_start'] ?? null, fn ($q, $v) => $q->where('available_from', '>=', $v))
            ->when($filters['available_from_end'] ?? null, fn ($q, $v) => $q->where('available_from', '<=', $v))
            ->when($filters['amenities'] ?? null, function ($q, $amenities) {
                foreach (explode(',', $amenities) as $id) {
                    $q->whereHas('amenities', fn ($aq) => $aq->where('amenities.id', (int) trim($id)));
                }
            });

        $sortBy = $filters['sort_by'] ?? null;
        match ($sortBy) {
            'price_asc'      => $query->orderBy('price', 'asc'),
            'price_desc'     => $query->orderBy('price', 'desc'),
            'oldest'         => $query->orderBy('created_at', 'asc'),
            'available_soon' => $query->orderBy('available_from', 'asc'),
            default          => $query->latest(),
        };

        return $query->paginate(15);
    }

    public function findById(string $id): ?Listing
    {
        return Listing::with([
            'owner:id,name,phone', 'photos', 'amenities', 'listingType',
            'facing', 'division', 'district', 'upazila', 'union',
        ])->find($id);
    }

    public function findByOwner(string $ownerId, array $filters = []): LengthAwarePaginator
    {
        return Listing::with(['photos', 'listingType', 'facing'])
            ->withCount('chats as inquiries')
            ->where('owner_id', $ownerId)
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['type_id'] ?? null, fn ($q, $v) => $q->where('listing_type_id', $v))
            ->latest()
            ->paginate(15);
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

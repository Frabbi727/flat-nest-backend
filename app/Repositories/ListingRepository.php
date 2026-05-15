<?php

namespace App\Repositories;

use App\Contracts\Repositories\ListingRepositoryInterface;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;

class ListingRepository implements ListingRepositoryInterface
{
    public function findActive(array $filters): Collection
    {
        return Listing::with(['owner:id,name,phone', 'photos'])
            ->where('status', 'active')
            ->when($filters['type'] ?? null,     fn ($q, $v) => $q->where('type', $v))
            ->when($filters['maxPrice'] ?? null,  fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($filters['amenities'] ?? null, function ($q, $amenities) {
                foreach (explode(',', $amenities) as $amenity) {
                    $q->whereJsonContains('amenities', trim($amenity));
                }
            })
            ->latest()
            ->get();
    }

    public function findById(string $id): ?Listing
    {
        return Listing::with(['owner:id,name,phone', 'photos'])->find($id);
    }

    public function findByOwner(string $ownerId): Collection
    {
        return Listing::with('photos')
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
        return $listing->fresh('photos');
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

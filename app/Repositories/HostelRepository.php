<?php

namespace App\Repositories;

use App\Contracts\Repositories\HostelRepositoryInterface;
use App\Enums\HostelStatus;
use App\Models\Hostel;
use Illuminate\Pagination\LengthAwarePaginator;

class HostelRepository implements HostelRepositoryInterface
{
    public function findActive(array $filters): LengthAwarePaginator
    {
        $query = Hostel::with(['type', 'photos', 'amenities', 'division', 'district', 'upazila'])
            ->withCount(['seats as total_seats', 'seats as vacant_seats' => fn ($q) => $q->where('status', 'vacant')])
            ->withAvg('reviews as avg_rating', 'rating')
            ->where('status', HostelStatus::Active)
            ->when($filters['search'] ?? null, function ($q, $search) {
                $term = '%' . $search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                      ->orWhere('address', 'like', $term)
                      ->orWhere('landmark', 'like', $term);
                });
            })
            ->when($filters['type_id'] ?? null, fn ($q, $v) => $q->where('hostel_type_id', $v))
            ->when($filters['gender_policy'] ?? null, function ($q, $v) {
                if ($v !== 'any') {
                    $q->where('gender_policy', $v);
                }
            })
            ->when($filters['price_min'] ?? null, fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($filters['price_max'] ?? null, fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($filters['price_unit'] ?? null, fn ($q, $v) => $q->where('price_unit', $v))
            ->when($filters['division_id'] ?? null, fn ($q, $v) => $q->where('division_id', $v))
            ->when($filters['district_id'] ?? null, fn ($q, $v) => $q->where('district_id', $v))
            ->when($filters['upazila_id'] ?? null, fn ($q, $v) => $q->where('upazila_id', $v))
            ->when(isset($filters['is_verified']) && $filters['is_verified'] !== '', fn ($q) => $q->where('is_verified', (bool) $filters['is_verified']))
            ->when($filters['amenities'] ?? null, function ($q, $amenities) {
                foreach (explode(',', $amenities) as $id) {
                    $q->whereHas('amenities', fn ($aq) => $aq->where('amenities.id', (int) trim($id)));
                }
            })
            ->when($filters['min_vacant_seats'] ?? null, function ($q, $v) {
                $q->whereHas('seats', fn ($sq) => $sq->where('status', 'vacant'), '>=', (int) $v);
            });

        $sortBy = $filters['sort_by'] ?? null;
        match ($sortBy) {
            'price_asc'   => $query->orderBy('price', 'asc'),
            'price_desc'  => $query->orderBy('price', 'desc'),
            'rating'      => $query->orderByDesc('avg_rating'),
            'most_vacant' => $query->orderByDesc('vacant_seats'),
            default       => $query->latest(),
        };

        return $query->paginate(15);
    }

    public function findById(string $id): ?Hostel
    {
        return Hostel::with([
            'type',
            'photos',
            'amenities',
            'division',
            'district',
            'upazila',
            'union',
            'rooms.seats',
            'reviews.user:id,name,avatar_url',
        ])
        ->withCount(['seats as total_seats', 'seats as vacant_seats' => fn ($q) => $q->where('status', 'vacant')])
        ->withAvg('reviews as avg_rating', 'rating')
        ->find($id);
    }

    public function findByOwner(string $ownerId, array $filters = []): LengthAwarePaginator
    {
        return Hostel::with(['type', 'photos'])
            ->withCount(['seats as total_seats', 'seats as vacant_seats' => fn ($q) => $q->where('status', 'vacant')])
            ->where('owner_id', $ownerId)
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15);
    }

    public function create(array $data): Hostel
    {
        return Hostel::create($data);
    }

    public function update(Hostel $hostel, array $data): Hostel
    {
        $hostel->update($data);
        return $hostel->fresh();
    }

    public function delete(Hostel $hostel): void
    {
        $hostel->delete();
    }

    public function incrementViews(Hostel $hostel): void
    {
        $hostel->increment('views');
    }
}

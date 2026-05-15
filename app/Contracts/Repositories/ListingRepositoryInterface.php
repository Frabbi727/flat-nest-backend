<?php

namespace App\Contracts\Repositories;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;

interface ListingRepositoryInterface
{
    public function findActive(array $filters): Collection;
    public function findById(string $id): ?Listing;
    public function findByOwner(string $ownerId): Collection;
    public function create(array $data): Listing;
    public function update(Listing $listing, array $data): Listing;
    public function delete(Listing $listing): void;
    public function incrementViews(Listing $listing): void;
}

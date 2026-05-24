<?php

namespace App\Contracts\Repositories;

use App\Models\Hostel;
use Illuminate\Pagination\LengthAwarePaginator;

interface HostelRepositoryInterface
{
    public function findActive(array $filters): LengthAwarePaginator;
    public function findById(string $id): ?Hostel;
    public function findByOwner(string $ownerId, array $filters = []): LengthAwarePaginator;
    public function create(array $data): Hostel;
    public function update(Hostel $hostel, array $data): Hostel;
    public function delete(Hostel $hostel): void;
    public function incrementViews(Hostel $hostel): void;
}

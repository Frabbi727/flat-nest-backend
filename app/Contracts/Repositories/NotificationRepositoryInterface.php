<?php

namespace App\Contracts\Repositories;

use App\Models\AppNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function forUser(string $userId): LengthAwarePaginator;
    public function findForUser(string $id, string $userId): ?AppNotification;
    public function create(array $data): AppNotification;
    public function markRead(AppNotification $notification): void;
    public function markAllRead(string $userId): void;
}

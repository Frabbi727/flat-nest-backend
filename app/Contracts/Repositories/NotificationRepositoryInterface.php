<?php

namespace App\Contracts\Repositories;

use App\Models\AppNotification;
use Illuminate\Database\Eloquent\Collection;

interface NotificationRepositoryInterface
{
    public function forUser(string $userId): Collection;
    public function findForUser(string $id, string $userId): ?AppNotification;
    public function create(array $data): AppNotification;
    public function markRead(AppNotification $notification): void;
    public function markAllRead(string $userId): void;
}

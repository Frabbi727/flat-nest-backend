<?php

namespace App\Repositories;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Models\AppNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function forUser(string $userId): LengthAwarePaginator
    {
        return AppNotification::where('user_id', $userId)->latest()->paginate(20);
    }

    public function findForUser(string $id, string $userId): ?AppNotification
    {
        return AppNotification::where('id', $id)->where('user_id', $userId)->first();
    }

    public function create(array $data): AppNotification
    {
        return AppNotification::create($data);
    }

    public function markRead(AppNotification $notification): void
    {
        $notification->update(['is_unread' => false]);
    }

    public function markAllRead(string $userId): void
    {
        AppNotification::where('user_id', $userId)->where('is_unread', true)->update(['is_unread' => false]);
    }
}

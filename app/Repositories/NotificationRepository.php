<?php

namespace App\Repositories;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Models\AppNotification;
use App\Services\FcmService;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(private readonly FcmService $fcm) {}

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
        $notification = AppNotification::create($data);

        $this->fcm->sendToUser(
            $notification->user_id,
            $notification->title,
            $notification->body,
            [
                'kind'         => $notification->kind,
                'reference_id' => $notification->reference_id,
            ]
        );

        return $notification;
    }

    public function markRead(AppNotification $notification): void
    {
        $notification->update(['is_unread' => false]);
    }

    public function markAllRead(string $userId): void
    {
        AppNotification::where('user_id', $userId)->where('is_unread', true)->update(['is_unread' => false]);
    }

    public function unreadCountForUser(string $userId): int
    {
        return AppNotification::where('user_id', $userId)->where('is_unread', true)->count();
    }
}

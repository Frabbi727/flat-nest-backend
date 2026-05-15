<?php

namespace App\Services;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Models\AppNotification;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationService
{
    public function __construct(private readonly NotificationRepositoryInterface $notifications) {}

    public function getForUser(string $userId): Collection
    {
        return $this->notifications->forUser($userId);
    }

    public function markRead(string $notificationId, string $userId): void
    {
        $notification = $this->notifications->findForUser($notificationId, $userId);

        if (! $notification) {
            throw new NotFoundHttpException('Notification not found');
        }

        $this->notifications->markRead($notification);
    }

    public function markAllRead(string $userId): void
    {
        $this->notifications->markAllRead($userId);
    }
}

<?php

namespace App\Services;

use App\Models\DeviceSession;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmService
{
    public function __construct(private readonly Messaging $messaging) {}

    public function sendToUser(string $userId, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceSession::where('user_id', $userId)
            ->whereNull('logged_out_at')
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData($data);
                $this->messaging->send($message);
            } catch (\Throwable) {
                // Silently fail — FCM errors must not break main flow
            }
        }
    }
}

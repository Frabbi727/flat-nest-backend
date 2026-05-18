<?php

namespace App\Services;

use App\Models\DeviceSession;
use Illuminate\Support\Facades\Log;
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

        Log::info('[FCM] sendToUser', [
            'user_id'     => $userId,
            'title'       => $title,
            'token_count' => count($tokens),
        ]);

        if (empty($tokens)) {
            Log::warning('[FCM] No active tokens found for user', ['user_id' => $userId]);
            return;
        }

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::new()
                    ->withToken($token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData($data);
                $this->messaging->send($message);
                Log::info('[FCM] Sent successfully', ['token' => substr($token, 0, 20) . '...']);
            } catch (\Throwable $e) {
                Log::error('[FCM] Send failed', [
                    'token' => substr($token, 0, 20) . '...',
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

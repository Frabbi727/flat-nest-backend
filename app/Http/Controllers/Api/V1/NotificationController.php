<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = AppNotification::where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'kind'       => $n->kind,
                'title'      => $n->title,
                'body'       => $n->body,
                'time'       => $n->created_at->diffForHumans(),
                'is_unread'  => $n->is_unread,
                'reference_id' => $n->reference_id,
            ]);

        return response()->json($notifications);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = AppNotification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $notification) {
            return response()->json(['message' => 'Notification not found', 'code' => 'NOT_FOUND'], 404);
        }

        $notification->update(['is_unread' => false]);

        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        AppNotification::where('user_id', $request->user()->id)
            ->where('is_unread', true)
            ->update(['is_unread' => false]);

        return response()->json(['message' => 'All marked as read']);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $paginator = $this->notifications->getForUser($userId);
        $unread    = $this->notifications->unreadCount($userId);

        return ApiResponse::paginated(
            NotificationResource::collection($paginator),
            $paginator,
            null,
            ['unread_count' => $unread]
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'unread_count' => $this->notifications->unreadCount($request->user()->id),
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $this->notifications->markRead($id, $request->user()->id);
        return ApiResponse::success(null, 'Marked as read');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->notifications->markAllRead($request->user()->id);
        return ApiResponse::success(null, 'All marked as read');
    }
}

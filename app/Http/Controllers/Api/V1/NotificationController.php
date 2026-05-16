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
        $paginator = $this->notifications->getForUser($request->user()->id);
        return ApiResponse::paginated(NotificationResource::collection($paginator), $paginator);
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

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return NotificationResource::collection(
            $this->notifications->getForUser($request->user()->id)
        );
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        try {
            $this->notifications->markRead($id, $request->user()->id);
            return response()->json(['message' => 'Marked as read']);
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        }
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->notifications->markAllRead($request->user()->id);
        return response()->json(['message' => 'All marked as read']);
    }
}

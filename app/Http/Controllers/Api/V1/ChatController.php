<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\StartChatRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return ChatResource::collection(
            $this->chat->getInboxForUser($request->user()->id)
        );
    }

    public function start(StartChatRequest $request): JsonResponse
    {
        try {
            $result = $this->chat->startChat($request->user(), $request->listing_id, $request->initial_message);
            return response()->json($result, 201);
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'FORBIDDEN'], 403);
        }
    }

    public function messages(Request $request, string $id): JsonResponse
    {
        try {
            $result = $this->chat->getMessages($id, $request->user()->id);
            return response()->json([
                'chat'     => $result['chat'],
                'messages' => MessageResource::collection($result['messages']),
            ]);
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        }
    }

    public function sendMessage(SendMessageRequest $request, string $id): JsonResponse
    {
        try {
            $message = $this->chat->sendMessage($id, $request->user(), $request->text);
            return response()->json(new MessageResource($message), 201);
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage(), 'code' => 'NOT_FOUND'], 404);
        }
    }
}

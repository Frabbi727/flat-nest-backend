<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\StartChatRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            ChatResource::collection($this->chat->getInboxForUser($request->user()->id))
        );
    }

    public function start(StartChatRequest $request): JsonResponse
    {
        $result = $this->chat->startChat($request->user(), $request->listing_id, $request->initial_message);
        return ApiResponse::success(['chat_id' => $result['chat']->id], null, 201);
    }

    public function messages(Request $request, string $id): JsonResponse
    {
        $result = $this->chat->getMessages($id, $request->user()->id);
        return ApiResponse::success([
            'chat'     => ['id' => $result['chat']->id],
            'messages' => MessageResource::collection($result['messages']),
        ]);
    }

    public function sendMessage(SendMessageRequest $request, string $id): JsonResponse
    {
        $message = $this->chat->sendMessage($id, $request->user(), $request->text);
        return ApiResponse::success(new MessageResource($message), null, 201);
    }
}

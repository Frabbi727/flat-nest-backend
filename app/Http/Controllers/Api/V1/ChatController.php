<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Chat;
use App\Models\Listing;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $chats = Chat::with(['listing:id,title,area', 'renter:id,name,avatar_url', 'owner:id,name,avatar_url', 'lastMessage'])
            ->where('renter_id', $userId)
            ->orWhere('owner_id', $userId)
            ->latest('updated_at')
            ->get()
            ->map(function ($chat) use ($userId) {
                return [
                    'id'           => $chat->id,
                    'listing'      => $chat->listing,
                    'other_user'   => $chat->renter_id === $userId ? $chat->owner : $chat->renter,
                    'last_message' => $chat->lastMessage,
                    'unread_count' => $chat->unreadFor($userId)->count(),
                    'updated_at'   => $chat->updated_at,
                ];
            });

        return response()->json($chats);
    }

    public function start(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'listing_id'      => 'required|uuid|exists:listings,id',
            'initial_message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 'VALIDATION_ERROR'], 400);
        }

        $listing = Listing::findOrFail($request->listing_id);

        if ($listing->owner_id === $request->user()->id) {
            return response()->json(['message' => 'You cannot chat about your own listing', 'code' => 'FORBIDDEN'], 403);
        }

        $chat = Chat::firstOrCreate([
            'renter_id'  => $request->user()->id,
            'owner_id'   => $listing->owner_id,
            'listing_id' => $request->listing_id,
        ]);

        $message = Message::create([
            'chat_id'   => $chat->id,
            'sender_id' => $request->user()->id,
            'text'      => $request->initial_message,
        ]);

        $this->notifyRecipient($chat->owner_id, $request->user()->name, $request->initial_message, $chat->id);

        $chat->touch();

        return response()->json(['chat' => $chat->load('listing'), 'message' => $message], 201);
    }

    public function messages(Request $request, string $id): JsonResponse
    {
        $chat = $this->findChatForUser($id, $request->user()->id);

        if (! $chat) {
            return response()->json(['message' => 'Chat not found', 'code' => 'NOT_FOUND'], 404);
        }

        $chat->unreadFor($request->user()->id)->update(['is_read' => true]);

        return response()->json([
            'chat'     => $chat->only(['id', 'listing_id', 'renter_id', 'owner_id']),
            'messages' => $chat->messages()->with('sender:id,name,avatar_url')->get(),
        ]);
    }

    public function sendMessage(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'code' => 'VALIDATION_ERROR'], 400);
        }

        $chat = $this->findChatForUser($id, $request->user()->id);

        if (! $chat) {
            return response()->json(['message' => 'Chat not found', 'code' => 'NOT_FOUND'], 404);
        }

        $message = Message::create([
            'chat_id'   => $chat->id,
            'sender_id' => $request->user()->id,
            'text'      => $request->text,
        ]);

        $recipientId = $chat->renter_id === $request->user()->id ? $chat->owner_id : $chat->renter_id;
        $this->notifyRecipient($recipientId, $request->user()->name, $request->text, $chat->id);

        $chat->touch();

        return response()->json($message->load('sender:id,name,avatar_url'), 201);
    }

    private function findChatForUser(string $chatId, string $userId): ?Chat
    {
        return Chat::where('id', $chatId)
            ->where(fn ($q) => $q->where('renter_id', $userId)->orWhere('owner_id', $userId))
            ->first();
    }

    private function notifyRecipient(string $recipientId, string $senderName, string $text, string $chatId): void
    {
        AppNotification::create([
            'user_id'      => $recipientId,
            'kind'         => 'message',
            'title'        => 'New message from ' . $senderName,
            'body'         => Str::limit($text, 80),
            'reference_id' => $chatId,
        ]);
    }
}

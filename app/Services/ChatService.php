<?php

namespace App\Services;

use App\Contracts\Repositories\ChatRepositoryInterface;
use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Listing;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChatService
{
    public function __construct(
        private readonly ChatRepositoryInterface $chats,
        private readonly NotificationRepositoryInterface $notifications,
    ) {}

    public function getInboxForUser(string $userId): Collection
    {
        return $this->chats->findForUser($userId);
    }

    public function startChat(User $renter, string $listingId, string $initialMessage): array
    {
        $listing = Listing::find($listingId);

        if (! $listing) {
            throw new NotFoundHttpException('Listing not found');
        }

        if ($listing->owner_id === $renter->id) {
            throw new AccessDeniedHttpException('You cannot chat about your own listing');
        }

        $chat = $this->chats->firstOrCreate([
            'renter_id'  => $renter->id,
            'owner_id'   => $listing->owner_id,
            'listing_id' => $listingId,
        ], ['status' => 'pending']);

        // Only create a message and notify if the chat is new (pending)
        if ($chat->wasRecentlyCreated) {
            $message = $this->chats->createMessage($chat->id, $renter->id, $initialMessage);
            $this->dispatchChatRequestNotification($listing->owner_id, $renter->name, $listing->title, $chat->id);
            return ['chat' => $chat, 'message' => $message];
        }

        // If chat exists, just return it without creating a new message or notification
        return ['chat' => $chat];
    }

    public function getMessages(string $chatId, string $userId): array
    {
        $chat = $this->chats->findChatForUser($chatId, $userId);

        if (! $chat) {
            throw new NotFoundHttpException('Chat not found');
        }

        $this->chats->markMessagesRead($chat, $userId);

        return [
            'chat'     => $chat,
            'messages' => $chat->messages()->with('sender:id,name,avatar_url')->get(),
        ];
    }

    public function sendMessage(string $chatId, User $sender, string $text): Message
    {
        $chat = $this->chats->findChatForUser($chatId, $sender->id);

        if (! $chat) {
            throw new NotFoundHttpException('Chat not found');
        }

        if ($chat->status !== 'accepted') {
            throw new AccessDeniedHttpException('This chat has not been accepted.');
        }

        $message     = $this->chats->createMessage($chat->id, $sender->id, $text);
        $recipientId = $chat->renter_id === $sender->id ? $chat->owner_id : $chat->renter_id;

        $this->dispatchMessageNotification($recipientId, $sender->name, $text, $chat->id);
        broadcast(new MessageSent($message))->toOthers();

        $chat->touch();

        return $message->load('sender:id,name,avatar_url');
    }

    public function acceptChat(string $chatId, User $user): void
    {
        $chat = $this->chats->find($chatId);

        if (!$chat || $chat->owner_id !== $user->id) {
            throw new AccessDeniedHttpException('You are not authorized to accept this chat.');
        }

        if ($chat->status !== 'pending') {
            return; // Or throw an exception if you want to be stricter
        }

        $chat->update(['status' => 'accepted']);

        $this->dispatchStatusNotification($chat->renter_id, $user->name, 'accepted', $chat->id);
    }

    public function rejectChat(string $chatId, User $user): void
    {
        $chat = $this->chats->find($chatId);

        if (!$chat || $chat->owner_id !== $user->id) {
            throw new AccessDeniedHttpException('You are not authorized to reject this chat.');
        }

        if ($chat->status !== 'pending') {
            return;
        }

        $chat->update(['status' => 'rejected']);

        $this->dispatchStatusNotification($chat->renter_id, $user->name, 'rejected', $chat->id);
    }

    private function dispatchChatRequestNotification(string $recipientId, string $senderName, string $listingTitle, string $chatId): void
    {
        $this->notifications->create([
            'user_id'      => $recipientId,
            'kind'         => 'chat_request',
            'title'        => 'New Chat Request',
            'body'         => "$senderName wants to chat about $listingTitle.",
            'reference_id' => $chatId,
        ]);
    }

    private function dispatchMessageNotification(string $recipientId, string $senderName, string $text, string $chatId): void
    {
        $this->notifications->create([
            'user_id'      => $recipientId,
            'kind'         => 'message',
            'title'        => 'New message from ' . $senderName,
            'body'         => Str::limit($text, 80),
            'reference_id' => $chatId,
        ]);
    }

    private function dispatchStatusNotification(string $recipientId, string $ownerName, string $status, string $chatId): void
    {
        $this->notifications->create([
            'user_id'      => $recipientId,
            'kind'         => "chat_$status",
            'title'        => "Chat Request " . ucfirst($status),
            'body'         => "$ownerName has $status your chat request.",
            'reference_id' => $chatId,
        ]);
    }
}

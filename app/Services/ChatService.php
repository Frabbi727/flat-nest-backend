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
        ]);

        $message = $this->chats->createMessage($chat->id, $renter->id, $initialMessage);

        $this->dispatchNotification($listing->owner_id, $renter->name, $initialMessage, $chat->id);
        broadcast(new MessageSent($message))->toOthers();

        $chat->touch();

        return ['chat' => $chat->load('listing'), 'message' => $message];
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

        $message     = $this->chats->createMessage($chat->id, $sender->id, $text);
        $recipientId = $chat->renter_id === $sender->id ? $chat->owner_id : $chat->renter_id;

        $this->dispatchNotification($recipientId, $sender->name, $text, $chat->id);
        broadcast(new MessageSent($message))->toOthers();

        $chat->touch();

        return $message->load('sender:id,name,avatar_url');
    }

    private function dispatchNotification(string $recipientId, string $senderName, string $text, string $chatId): void
    {
        $this->notifications->create([
            'user_id'      => $recipientId,
            'kind'         => 'message',
            'title'        => 'New message from ' . $senderName,
            'body'         => Str::limit($text, 80),
            'reference_id' => $chatId,
        ]);
    }
}

<?php

namespace App\Repositories;

use App\Contracts\Repositories\ChatRepositoryInterface;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class ChatRepository implements ChatRepositoryInterface
{
    public function findForUser(string $userId): Collection
    {
        return Chat::with(['listing:id,title,area', 'renter:id,name,avatar_url', 'owner:id,name,avatar_url', 'lastMessage'])
            ->where('renter_id', $userId)
            ->orWhere('owner_id', $userId)
            ->latest('updated_at')
            ->get();
    }

    public function findById(string $chatId): ?Chat
    {
        return Chat::with(['listing', 'renter:id,name,avatar_url', 'owner:id,name,avatar_url'])->find($chatId);
    }

    public function findChatForUser(string $chatId, string $userId): ?Chat
    {
        return Chat::where('id', $chatId)
            ->where(fn ($q) => $q->where('renter_id', $userId)->orWhere('owner_id', $userId))
            ->first();
    }

    public function firstOrCreate(array $participants): Chat
    {
        return Chat::firstOrCreate([
            'renter_id'  => $participants['renter_id'],
            'owner_id'   => $participants['owner_id'],
            'listing_id' => $participants['listing_id'],
        ]);
    }

    public function createMessage(string $chatId, string $senderId, string $text): Message
    {
        return Message::create([
            'chat_id'   => $chatId,
            'sender_id' => $senderId,
            'text'      => $text,
        ]);
    }

    public function markMessagesRead(Chat $chat, string $userId): void
    {
        $chat->unreadFor($userId)->update(['is_read' => true]);
    }
}

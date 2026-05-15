<?php

namespace App\Contracts\Repositories;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

interface ChatRepositoryInterface
{
    public function findForUser(string $userId): Collection;
    public function findById(string $chatId): ?Chat;
    public function findChatForUser(string $chatId, string $userId): ?Chat;
    public function firstOrCreate(array $participants): Chat;
    public function createMessage(string $chatId, string $senderId, string $text): Message;
    public function markMessagesRead(Chat $chat, string $userId): void;
}

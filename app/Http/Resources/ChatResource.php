<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userId    = $request->user()->id;
        $isRenter  = $this->renter_id === $userId;
        $otherUser = $isRenter ? $this->owner : $this->renter;

        return [
            'id'           => $this->id,
            'listing'      => $this->listing ? ['id' => $this->listing->id, 'title' => $this->listing->title, 'area' => $this->listing->area] : null,
            'other_user'   => new UserResource($otherUser),
            'last_message' => $this->lastMessage ? new MessageResource($this->lastMessage) : null,
            'unread_count' => $this->unreadFor($userId)->count(),
            'updated_at'   => $this->updated_at,
        ];
    }
}

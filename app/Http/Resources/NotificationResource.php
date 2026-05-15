<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'kind'         => $this->kind,
            'title'        => $this->title,
            'body'         => $this->body,
            'time'         => $this->created_at->diffForHumans(),
            'is_unread'    => $this->is_unread,
            'reference_id' => $this->reference_id,
        ];
    }
}

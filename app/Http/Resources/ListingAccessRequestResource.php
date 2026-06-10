<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingAccessRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'status'     => $this->status->value,
            'listing'    => $this->whenLoaded('listing', fn () => [
                'id'    => $this->listing->id,
                'title' => $this->listing->title,
            ]),
            'requester'  => $this->whenLoaded('requester', fn () => [
                'id'         => $this->requester->id,
                'name'       => $this->requester->name,
                'avatar_url' => $this->requester->avatar_url,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}

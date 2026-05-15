<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'area'           => $this->area,
            'road_and_house' => $this->when($this->road_and_house, $this->road_and_house),
            'type'           => $this->type,
            'price'          => $this->price,
            'deposit'        => $this->deposit,
            'beds'           => $this->beds,
            'baths'          => $this->baths,
            'size'           => $this->size,
            'description'    => $this->description,
            'amenities'      => $this->amenities ?? [],
            'status'         => $this->status,
            'views'          => $this->views,
            'coord_x'        => $this->coord_x,
            'coord_y'        => $this->coord_y,
            'owner'          => new UserResource($this->whenLoaded('owner')),
            'photos'         => ListingPhotoResource::collection($this->whenLoaded('photos')),
            'created_at'     => $this->created_at,
        ];
    }
}

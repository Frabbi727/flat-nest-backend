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
            'division_id'    => $this->division_id,
            'district_id'    => $this->district_id,
            'upazila_id'     => $this->upazila_id,
            'union_id'       => $this->union_id,
            'listing_type'   => $this->whenLoaded('listingType', fn () => [
                'id'    => $this->listingType->id,
                'name'  => $this->listingType->name,
                'label' => $this->listingType->label,
            ]),
            'price'          => $this->price,
            'deposit'        => $this->deposit,
            'beds'           => $this->beds,
            'baths'          => $this->baths,
            'size'           => $this->size,
            'description'    => $this->description,
            'amenities'      => $this->whenLoaded('amenities', fn () =>
                $this->amenities->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'label' => $a->label])
            ),
            'status'         => $this->status->value,
            'status_label'   => $this->status->label(),
            'views'          => $this->views,
            'coord_x'        => $this->coord_x,
            'coord_y'        => $this->coord_y,
            'owner'          => new UserResource($this->whenLoaded('owner')),
            'photos'         => ListingPhotoResource::collection($this->whenLoaded('photos')),
            'created_at'     => $this->created_at,
        ];
    }
}

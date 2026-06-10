<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    private function canSeePrivate(): bool
    {
        return $this->resource->user_has_access ?? false;
    }

    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'owner_id'          => $this->owner_id,
            'title'             => $this->title,
            'listing_type_id'   => $this->listing_type_id,
            'listing_type'      => $this->whenLoaded('listingType', fn () => [
                'id'    => $this->listingType->id,
                'label' => $this->listingType->label,
                'slug'  => $this->listingType->name,
            ]),
            'price'             => $this->price,
            'deposit'           => $this->deposit,
            'available_from'    => $this->available_from?->format('Y-m-d'),
            'beds'              => $this->beds,
            'baths'             => $this->baths,
            'size'              => $this->size,
            'floor_no'          => $this->floor_no,
            'facing_id'         => $this->facing_id,
            'facing'            => $this->whenLoaded('facing', fn () => [
                'id'    => $this->facing->id,
                'label' => $this->facing->label,
                'slug'  => $this->facing->slug,
            ]),
            'description'       => $this->description,
            'division_id'       => $this->division_id,
            'district_id'       => $this->district_id,
            'upazila_id'        => $this->upazila_id,
            'union_id'          => $this->union_id,
            'division'          => $this->whenLoaded('division', fn () => [
                'id'   => $this->division->id,
                'name' => $this->division->name,
            ]),
            'district'          => $this->whenLoaded('district', fn () => [
                'id'   => $this->district->id,
                'name' => $this->district->name,
            ]),
            'upazila'           => $this->whenLoaded('upazila', fn () => [
                'id'   => $this->upazila->id,
                'name' => $this->upazila->name,
            ]),
            'union'             => $this->whenLoaded('union', fn () => [
                'id'   => $this->union->id,
                'name' => $this->union->name,
            ]),
            'area'                  => $this->area,
            'road'                  => $this->when($this->canSeePrivate(), $this->road),
            'house_name'            => $this->when($this->canSeePrivate(), $this->house_name),
            'block'                 => $this->when($this->canSeePrivate(), $this->block),
            'section'               => $this->when($this->canSeePrivate(), $this->section),
            'coord_x'               => $this->when($this->canSeePrivate(), $this->coord_x),
            'coord_y'               => $this->when($this->canSeePrivate(), $this->coord_y),
            'owner_name'            => $this->when($this->canSeePrivate(), $this->owner_name),
            'owner_phone'           => $this->when($this->canSeePrivate(), $this->owner_phone),
            'owner_alt_phone'       => $this->when($this->canSeePrivate(), $this->owner_alt_phone),
            'owner_email'           => $this->when($this->canSeePrivate(), $this->owner_email),
            'preferred_contact'     => $this->when($this->canSeePrivate(), $this->preferred_contact),
            'access_request_status' => $this->access_request_status ?? null,
            'status'                => $this->status->value,
            'status_label'          => $this->status->label(),
            'rejection_reason'      => $this->rejection_reason,
            'views'                 => $this->views,
            'owner'                 => $this->when($this->canSeePrivate(), new UserResource($this->whenLoaded('owner'))),
            'photos'            => ListingPhotoResource::collection($this->whenLoaded('photos')),
            'amenities'         => $this->whenLoaded('amenities', fn () =>
                $this->amenities->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'label' => $a->label])
            ),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}

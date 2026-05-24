<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostelDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'description'      => $this->description,
            'hostel_type_id'   => $this->hostel_type_id,
            'type'             => $this->whenLoaded('type', fn () => [
                'id'    => $this->type->id,
                'name'  => $this->type->name,
                'label' => $this->type->label,
            ]),
            'gender_policy'    => $this->gender_policy,
            'price'            => $this->price,
            'price_unit'       => $this->price_unit,
            'advance'          => $this->advance,
            'meal_included'    => $this->meal_included,
            'meal_price'       => $this->meal_price,
            'curfew_time'      => $this->curfew_time,
            'floor'            => $this->floor,
            'building'         => $this->building,
            'landmark'         => $this->landmark,
            'landmark_distance'=> $this->landmark_distance,
            'address'          => $this->address,
            'rules'            => $this->rules ?? [],
            'division_id'      => $this->division_id,
            'district_id'      => $this->district_id,
            'upazila_id'       => $this->upazila_id,
            'union_id'         => $this->union_id,
            'division'         => $this->whenLoaded('division', fn () => ['id' => $this->division->id, 'name' => $this->division->name]),
            'district'         => $this->whenLoaded('district', fn () => ['id' => $this->district->id, 'name' => $this->district->name]),
            'upazila'          => $this->whenLoaded('upazila', fn () => ['id' => $this->upazila->id, 'name' => $this->upazila->name]),
            'coord_x'          => $this->coord_x,
            'coord_y'          => $this->coord_y,
            'owner_name'       => $this->owner_name,
            'owner_phone'      => $this->owner_phone,
            'is_verified'      => $this->is_verified,
            'views'            => $this->views,
            'status'           => $this->status->value,
            'status_label'     => $this->status->label(),
            'rejection_reason' => $this->rejection_reason,
            'avg_rating'       => round((float) ($this->avg_rating ?? 0), 1),
            'total_seats'      => $this->total_seats ?? 0,
            'vacant_seats'     => $this->vacant_seats ?? 0,
            'photos'           => HostelPhotoResource::collection($this->whenLoaded('photos')),
            'amenities'        => $this->whenLoaded('amenities', fn () =>
                $this->amenities->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'label' => $a->label])
            ),
            'rooms'            => $this->whenLoaded('rooms', fn () =>
                $this->rooms->map(fn ($room) => [
                    'id'    => $room->id,
                    'name'  => $room->name,
                    'seats' => $room->seats->map(fn ($seat) => [
                        'id'          => $seat->id,
                        'seat_number' => $seat->seat_number,
                        'status'      => $seat->status,
                    ]),
                ])
            ),
            'reviews'          => $this->whenLoaded('reviews', fn () =>
                $this->reviews->map(fn ($r) => [
                    'id'         => $r->id,
                    'rating'     => $r->rating,
                    'comment'    => $r->comment,
                    'created_at' => $r->created_at,
                    'user'       => $r->user ? [
                        'id'         => $r->user->id,
                        'name'       => $r->user->name,
                        'avatar_url' => $r->user->avatar_url,
                    ] : null,
                ])
            ),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}

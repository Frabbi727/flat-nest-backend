<?php

namespace App\Services;

use App\Contracts\Repositories\HostelRepositoryInterface;
use App\Enums\HostelStatus;
use App\Models\Hostel;
use App\Models\HostelPhoto;
use App\Models\HostelRoom;
use App\Models\HostelSeat;
use App\Models\HostelReview;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class HostelService
{
    public function __construct(
        private readonly HostelRepositoryInterface $hostels,
    ) {}

    public function getFeed(array $filters): LengthAwarePaginator
    {
        return $this->hostels->findActive($filters);
    }

    public function getById(string $id): Hostel
    {
        $hostel = $this->hostels->findById($id);

        if (! $hostel) {
            throw new NotFoundHttpException('Hostel not found');
        }

        $this->hostels->incrementViews($hostel);

        return $hostel;
    }

    public function getOwnerDashboard(string $ownerId, array $filters = []): LengthAwarePaginator
    {
        return $this->hostels->findByOwner($ownerId, $filters);
    }

    public function create(string $ownerId, array $data): Hostel
    {
        $hostel = $this->hostels->create([
            'owner_id'      => $ownerId,
            'hostel_type_id'=> $data['hostel_type_id'],
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'gender_policy' => $data['gender_policy'] ?? 'mixed',
            'price'         => $data['price'],
            'price_unit'    => $data['price_unit'] ?? 'seat/month',
            'advance'       => $data['advance'] ?? null,
            'meal_included' => $data['meal_included'] ?? false,
            'meal_price'    => $data['meal_price'] ?? null,
            'curfew_time'   => $data['curfew_time'] ?? null,
            'floor'         => $data['floor'] ?? null,
            'building'      => $data['building'] ?? null,
            'rules'         => $data['rules'] ?? null,
            'owner_name'    => $data['owner_name'] ?? null,
            'owner_phone'   => $data['owner_phone'] ?? null,
            'status'        => HostelStatus::Draft,
        ]);

        if (! empty($data['amenities'])) {
            $hostel->amenities()->sync($data['amenities']);
        }

        return $hostel->load(['type', 'photos', 'amenities']);
    }

    public function addPhotos(string $hostelId, string $ownerId, array $photos): Hostel
    {
        $hostel = $this->resolveOwned($hostelId, $ownerId);

        $existingCount = $hostel->photos()->count();

        foreach ($photos as $index => $photo) {
            /** @var UploadedFile $photo */
            $path = Storage::disk('public')->put('hostels/' . $hostel->id, $photo);
            $url  = Storage::disk('public')->url($path);

            HostelPhoto::create([
                'hostel_id' => $hostel->id,
                'url'       => $url,
                'position'  => $existingCount + $index,
            ]);
        }

        return $hostel->fresh(['photos']);
    }

    public function updateLocation(string $hostelId, string $ownerId, array $data): Hostel
    {
        $hostel = $this->resolveOwned($hostelId, $ownerId);

        $this->hostels->update($hostel, array_intersect_key($data, array_flip([
            'address', 'landmark', 'landmark_distance',
            'division_id', 'district_id', 'upazila_id', 'union_id',
            'coord_x', 'coord_y',
        ])));

        return $hostel->fresh(['division', 'district', 'upazila', 'union']);
    }

    public function addRoom(string $hostelId, string $ownerId, array $data): HostelRoom
    {
        $hostel = $this->resolveOwned($hostelId, $ownerId);

        $position = $hostel->rooms()->count();

        $room = HostelRoom::create([
            'hostel_id' => $hostel->id,
            'name'      => $data['name'],
            'position'  => $position,
        ]);

        if (! empty($data['seats'])) {
            foreach ($data['seats'] as $seat) {
                HostelSeat::create([
                    'hostel_id'   => $hostel->id,
                    'room_id'     => $room->id,
                    'seat_number' => $seat['seat_number'],
                    'status'      => $seat['status'] ?? 'vacant',
                ]);
            }
        }

        return $room->load('seats');
    }

    public function updateRoom(string $hostelId, string $ownerId, string $roomId, array $data): HostelRoom
    {
        $this->resolveOwned($hostelId, $ownerId);

        $room = HostelRoom::where('id', $roomId)->where('hostel_id', $hostelId)->firstOrFail();
        $room->update(array_intersect_key($data, array_flip(['name'])));

        return $room->fresh('seats');
    }

    public function updateSeat(string $hostelId, string $ownerId, string $seatId, array $data): HostelSeat
    {
        $this->resolveOwned($hostelId, $ownerId);

        $seat = HostelSeat::where('id', $seatId)->where('hostel_id', $hostelId)->firstOrFail();
        $seat->update(array_intersect_key($data, array_flip(['status', 'seat_number'])));

        return $seat->fresh();
    }

    public function submit(string $hostelId, string $ownerId): Hostel
    {
        $hostel = $this->resolveOwned($hostelId, $ownerId);

        if (! in_array($hostel->status, [HostelStatus::Draft, HostelStatus::Rejected])) {
            throw new UnprocessableEntityHttpException('Only draft or rejected hostels can be submitted.');
        }

        $this->hostels->update($hostel, [
            'status'           => HostelStatus::Pending,
            'rejection_reason' => null,
        ]);

        return $hostel->fresh();
    }

    public function update(string $hostelId, string $ownerId, array $data): Hostel
    {
        $hostel = $this->resolveOwned($hostelId, $ownerId);

        $amenityIds = $data['amenities'] ?? null;
        $fields     = array_diff_key($data, ['amenities' => null]);

        $this->hostels->update($hostel, $fields);

        if ($amenityIds !== null) {
            $hostel->amenities()->sync($amenityIds);
        }

        return $hostel->fresh(['type', 'photos', 'amenities']);
    }

    public function delete(string $hostelId, string $ownerId): void
    {
        $hostel = $this->resolveOwned($hostelId, $ownerId);
        $this->hostels->delete($hostel);
    }

    public function addReview(string $hostelId, string $userId, array $data): HostelReview
    {
        $hostel = $this->hostels->findById($hostelId);

        if (! $hostel || $hostel->status !== HostelStatus::Active) {
            throw new NotFoundHttpException('Hostel not found');
        }

        $existing = HostelReview::where('hostel_id', $hostelId)->where('user_id', $userId)->first();
        if ($existing) {
            $existing->update(['rating' => $data['rating'], 'comment' => $data['comment'] ?? null]);
            return $existing->fresh('user');
        }

        $review = HostelReview::create([
            'hostel_id' => $hostelId,
            'user_id'   => $userId,
            'rating'    => $data['rating'],
            'comment'   => $data['comment'] ?? null,
        ]);

        return $review->load('user:id,name,avatar_url');
    }

    private function resolveOwned(string $hostelId, string $ownerId): Hostel
    {
        $hostel = $this->hostels->findById($hostelId);

        if (! $hostel) {
            throw new NotFoundHttpException('Hostel not found');
        }

        if ($hostel->owner_id !== $ownerId) {
            throw new AccessDeniedHttpException('You do not own this hostel');
        }

        return $hostel;
    }
}

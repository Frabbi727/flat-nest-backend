<?php

namespace App\Services;

use App\Contracts\Repositories\ListingRepositoryInterface;
use App\Enums\ListingStatus;
use App\Models\AppNotification;
use App\Models\Listing;
use App\Models\ListingPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ListingService
{
    public function __construct(private readonly ListingRepositoryInterface $listings) {}

    public function getFeed(array $filters): LengthAwarePaginator
    {
        return $this->listings->findActive($filters);
    }

    public function getById(string $id): Listing
    {
        $listing = $this->listings->findById($id);

        if (! $listing) {
            throw new NotFoundHttpException('Listing not found');
        }

        $this->listings->incrementViews($listing);

        return $listing->fresh([
            'owner:id,name,phone', 'photos', 'amenities', 'listingType',
            'facing', 'division', 'district', 'upazila', 'union',
        ]);
    }

    public function getOwnerDashboard(string $ownerId, array $filters = []): LengthAwarePaginator
    {
        return $this->listings->findByOwner($ownerId, $filters);
    }

    public function create(string $ownerId, array $data): Listing
    {
        $listing = $this->listings->create([
            'owner_id'        => $ownerId,
            'listing_type_id' => $data['listing_type_id'],
            'title'           => $data['title'],
            'price'           => $data['price'],
            'deposit'         => $data['deposit'] ?? null,
            'beds'            => $data['beds'],
            'baths'           => $data['baths'],
            'size'            => $data['size'] ?? null,
            'description'     => $data['description'] ?? null,
            'available_from'  => $data['available_from'] ?? null,
            'floor_no'        => $data['floor_no'] ?? null,
            'facing_id'       => $data['facing_id'] ?? null,
            'status'          => ListingStatus::Draft,
        ]);

        if (! empty($data['amenities'])) {
            $listing->amenities()->sync($data['amenities']);
        }

        return $listing->load(['listingType', 'facing', 'photos']);
    }

    public function addPhotos(string $listingId, string $ownerId, array $photos): Listing
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing) {
            throw new NotFoundHttpException('Listing not found');
        }

        if ($listing->owner_id !== $ownerId) {
            throw new AccessDeniedHttpException('You do not own this listing');
        }

        $existingCount = $listing->photos()->count();
        $this->attachPhotos($listing, $photos, startPosition: $existingCount);
        $this->revertToReviewIfActive($listing);

        return $listing->fresh(['owner:id,name,phone', 'photos']);
    }

    public function updateLocation(string $listingId, string $ownerId, array $data): Listing
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing || $listing->owner_id !== $ownerId) {
            throw new NotFoundHttpException('Listing not found');
        }

        $locationKeys = [
            'area', 'division_id', 'district_id', 'upazila_id', 'union_id',
            'coord_x', 'coord_y', 'road', 'house_name', 'block', 'section',
        ];
        $fields = array_intersect_key($data, array_flip($locationKeys));

        $hasChanges = $this->hasFieldChanges($listing, $fields);

        $this->listings->update($listing, $fields);
        $listing->refresh();

        if ($hasChanges) {
            $this->revertToReviewIfActive($listing);
        }

        return $listing->load(['listingType', 'facing', 'division', 'district', 'upazila', 'union']);
    }

    public function submit(string $listingId, string $ownerId): Listing
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing || $listing->owner_id !== $ownerId) {
            throw new NotFoundHttpException('Listing not found');
        }

        if (! in_array($listing->status, [ListingStatus::Draft, ListingStatus::Rejected])) {
            throw new UnprocessableEntityHttpException(
                'Only draft or rejected listings can be submitted.'
            );
        }

        if ($listing->photos()->count() === 0) {
            throw new UnprocessableEntityHttpException(
                'At least one photo is required. Please complete Step 2 first.'
            );
        }

        return $this->listings->update($listing, [
            'status'           => ListingStatus::Pending,
            'rejection_reason' => null,
        ]);
    }

    public function markAsRented(string $listingId, string $ownerId): Listing
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing || $listing->owner_id !== $ownerId) {
            throw new NotFoundHttpException('Listing not found');
        }

        if ($listing->status !== ListingStatus::Active) {
            throw new UnprocessableEntityHttpException(
                'Only active listings can be marked as rented.'
            );
        }

        return $this->listings->update($listing, ['status' => ListingStatus::Rented]);
    }

    public function update(string $listingId, string $ownerId, array $data): Listing
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing || $listing->owner_id !== $ownerId) {
            throw new NotFoundHttpException('Listing not found');
        }

        $amenityIds = $data['amenities'] ?? null;

        $allFields = array_diff_key($data, ['amenities' => null]);

        $hasFieldChanges = $this->hasFieldChanges($listing, $allFields);

        $this->listings->update($listing, $allFields);
        $listing->refresh();

        $amenityChanged = false;
        if ($amenityIds !== null) {
            $diff           = $listing->amenities()->sync($amenityIds);
            $amenityChanged = count($diff['attached']) > 0 || count($diff['detached']) > 0;
        }

        if ($hasFieldChanges || $amenityChanged) {
            $this->revertToReviewIfActive($listing);
        }

        return $listing->load([
            'listingType', 'facing', 'photos', 'amenities',
            'division', 'district', 'upazila', 'union',
        ]);
    }

    public function updateOwnerInfo(string $listingId, string $ownerId, array $data): Listing
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing || $listing->owner_id !== $ownerId) {
            throw new NotFoundHttpException('Listing not found');
        }

        $this->listings->update($listing, $data);

        return $listing->fresh();
    }

    public function delete(string $listingId, string $ownerId): void
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing || $listing->owner_id !== $ownerId) {
            throw new NotFoundHttpException('Listing not found');
        }

        $this->listings->delete($listing);
    }

    private function hasFieldChanges(Listing $listing, array $fields): bool
    {
        foreach ($fields as $key => $value) {
            if ((string) ($listing->$key ?? '') !== (string) ($value ?? '')) {
                return true;
            }
        }
        return false;
    }

    private function revertToReviewIfActive(Listing $listing): void
    {
        if ($listing->status !== ListingStatus::Active) {
            return;
        }

        $this->listings->update($listing, ['status' => ListingStatus::Pending]);

        AppNotification::create([
            'user_id'      => $listing->owner_id,
            'kind'         => 'listing',
            'title'        => 'Your listing is under re-review.',
            'body'         => 'You edited "' . $listing->title . '". It has been sent for re-approval and is temporarily hidden from the feed.',
            'reference_id' => $listing->id,
        ]);
    }

    private function attachPhotos(Listing $listing, array $photos, int $startPosition = 0): void
    {
        foreach ($photos as $index => $photo) {
            /** @var UploadedFile $photo */
            $path = Storage::disk(config('filesystems.default'))->put('listings/'.$listing->id, $photo);
            $url  = Storage::disk(config('filesystems.default'))->url($path);

            ListingPhoto::create([
                'listing_id' => $listing->id,
                'url'        => $url,
                'position'   => $startPosition + $index,
            ]);
        }
    }
}

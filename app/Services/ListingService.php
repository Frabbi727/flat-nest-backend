<?php

namespace App\Services;

use App\Contracts\Repositories\ListingRepositoryInterface;
use App\Models\Listing;
use App\Models\ListingPhoto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListingService
{
    public function __construct(private readonly ListingRepositoryInterface $listings) {}

    public function getFeed(array $filters): Collection
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

        return $listing->fresh(['owner:id,name,phone', 'photos']);
    }

    public function getOwnerDashboard(string $ownerId): Collection
    {
        return $this->listings->findByOwner($ownerId);
    }

    public function create(string $ownerId, array $data): Listing
    {
        return $this->listings->create([
            'owner_id'    => $ownerId,
            'title'       => $data['title'],
            'type'        => $data['type'],
            'price'       => $data['price'],
            'deposit'     => $data['deposit'] ?? null,
            'beds'        => $data['beds'],
            'baths'       => $data['baths'],
            'size'        => $data['size'] ?? null,
            'description' => $data['description'] ?? null,
            'amenities'   => $data['amenities'] ?? [],
            'status'      => 'draft',
        ]);
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

        return $listing->fresh(['owner:id,name,phone', 'photos']);
    }

    public function updateLocation(string $listingId, string $ownerId, array $data): Listing
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing || $listing->owner_id !== $ownerId) {
            throw new NotFoundHttpException('Listing not found');
        }

        return $this->listings->update($listing, [
            'area'           => $data['area'],
            'road_and_house' => $data['road_and_house'] ?? null,
            'coord_x'        => $data['coord_x'] ?? null,
            'coord_y'        => $data['coord_y'] ?? null,
        ]);
    }

    public function submit(string $listingId, string $ownerId): Listing
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing || $listing->owner_id !== $ownerId) {
            throw new NotFoundHttpException('Listing not found');
        }

        if (! $listing->area) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                'Location is required. Please complete Step 3 first.'
            );
        }

        if ($listing->photos()->count() === 0) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                'At least one photo is required. Please complete Step 2 first.'
            );
        }

        return $this->listings->update($listing, ['status' => 'pending']);
    }

    public function update(string $listingId, string $ownerId, array $data): Listing
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing || $listing->owner_id !== $ownerId) {
            throw new NotFoundHttpException('Listing not found');
        }

        return $this->listings->update($listing, $data);
    }

    public function delete(string $listingId, string $ownerId): void
    {
        $listing = $this->listings->findById($listingId);

        if (! $listing || $listing->owner_id !== $ownerId) {
            throw new NotFoundHttpException('Listing not found');
        }

        $this->listings->delete($listing);
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

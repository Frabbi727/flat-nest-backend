<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WishlistService
{
    public function getSavedIds(User $user): Collection
    {
        return $user->wishlist()->pluck('listings.id');
    }

    public function save(User $user, string $listingId): void
    {
        if (! Listing::find($listingId)) {
            throw new NotFoundHttpException('Listing not found');
        }

        $user->wishlist()->syncWithoutDetaching([$listingId]);
    }

    public function remove(User $user, string $listingId): void
    {
        $user->wishlist()->detach($listingId);
    }
}

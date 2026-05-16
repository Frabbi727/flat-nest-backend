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

    public function toggle(User $user, string $listingId): bool
    {
        if (! Listing::find($listingId)) {
            throw new NotFoundHttpException('Listing not found');
        }

        if ($user->wishlist()->where('listings.id', $listingId)->exists()) {
            $user->wishlist()->detach($listingId);
            return false; // removed
        }

        $user->wishlist()->attach($listingId);
        return true; // saved
    }
}

<?php

namespace App\Enums;

enum NotificationKind: string
{
    case ListingSubmitted       = 'listing_submitted';
    case ListingApproved        = 'listing_approved';
    case ListingRejected        = 'listing_rejected';
    case ListingReview          = 'listing_review';
    case NearbyListing          = 'nearby_listing';
    case WishlistListingRented  = 'wishlist_listing_rented';
    case ContactInfoRequested   = 'contact_info_requested';
    case ContactInfoGranted     = 'contact_info_granted';
    case ContactInfoDenied      = 'contact_info_denied';
}

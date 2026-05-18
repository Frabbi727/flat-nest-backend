<?php

namespace App\Enums;

enum NotificationKind: string
{
    case ListingSubmitted = 'listing_submitted';
    case ListingApproved  = 'listing_approved';
    case ListingRejected  = 'listing_rejected';
    case ListingReview    = 'listing_review';
}

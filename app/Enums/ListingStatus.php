<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft    = 'draft';
    case Pending  = 'pending';
    case Active   = 'active';
    case Rented   = 'rented';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft    => 'Draft',
            self::Pending  => 'Pending Review',
            self::Active   => 'Active',
            self::Rented   => 'Rented',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft    => 'gray',
            self::Pending  => 'warning',
            self::Active   => 'success',
            self::Rented   => 'info',
            self::Rejected => 'danger',
        };
    }
}
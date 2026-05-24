<?php

namespace App\Enums;

enum HostelStatus: string
{
    case Draft    = 'draft';
    case Pending  = 'pending';
    case Active   = 'active';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft    => 'Draft',
            self::Pending  => 'Pending Review',
            self::Active   => 'Active',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft    => 'gray',
            self::Pending  => 'warning',
            self::Active   => 'success',
            self::Rejected => 'danger',
        };
    }
}

<?php

namespace App\Enums;

enum UserRole: string
{
    case Renter = 'renter';
    case Owner  = 'owner';

    public function label(): string
    {
        return match ($this) {
            self::Renter => 'Renter',
            self::Owner  => 'Owner',
        };
    }
}

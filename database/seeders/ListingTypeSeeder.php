<?php

namespace Database\Seeders;

use App\Models\ListingType;
use Illuminate\Database\Seeder;

class ListingTypeSeeder extends Seeder
{
    private const TYPES = [
        ['name' => 'Family',   'label' => 'Family Flat'],
        ['name' => 'Bachelor', 'label' => 'Bachelor Room'],
        ['name' => 'Student',  'label' => 'Student Room'],
        ['name' => 'Couple',   'label' => 'Couple Flat'],
        ['name' => 'Sublet',   'label' => 'Sublet Room'],
    ];

    public function run(): void
    {
        ListingType::upsert(self::TYPES, uniqueBy: ['name'], update: ['label']);
    }
}

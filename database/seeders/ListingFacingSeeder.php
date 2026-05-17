<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ListingFacingSeeder extends Seeder
{
    private const FACINGS = [
        ['id' => 1, 'label' => 'North',      'slug' => 'north'],
        ['id' => 2, 'label' => 'South',      'slug' => 'south'],
        ['id' => 3, 'label' => 'East',       'slug' => 'east'],
        ['id' => 4, 'label' => 'West',       'slug' => 'west'],
        ['id' => 5, 'label' => 'North-East', 'slug' => 'north-east'],
        ['id' => 6, 'label' => 'North-West', 'slug' => 'north-west'],
        ['id' => 7, 'label' => 'South-East', 'slug' => 'south-east'],
        ['id' => 8, 'label' => 'South-West', 'slug' => 'south-west'],
    ];

    public function run(): void
    {
        DB::table('listing_facings')->upsert(
            self::FACINGS,
            uniqueBy: ['slug'],
            update: ['label']
        );
    }
}

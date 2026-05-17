<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            AmenitySeeder::class,
            ListingTypeSeeder::class,
            ListingFacingSeeder::class,
           // ListingSeeder::class,
            DivisionCsvSeeder::class,
            DistrictCsvSeeder::class,
            UpazilaCsvSeeder::class,
            UnionCsvSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ListingSeeder::class,
            DivisionCsvSeeder::class,
            DistrictCsvSeeder::class,
            UpazilaCsvSeeder::class,
            UnionCsvSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictCsvSeeder extends Seeder
{
    private const COLUMNS = ['id', 'division_id', 'name', 'bn_name', 'lat', 'lon', 'url'];

    public function run(): void
    {
        $rows = array_map('str_getcsv', file(database_path('seeders/csv/districts.csv')));

        $data = array_map(function ($row) {
            $r = array_combine(self::COLUMNS, $row);
            return [
                'id'          => $r['id'],
                'division_id' => $r['division_id'],
                'name'        => $r['name'],
                'bn_name'     => $r['bn_name'],
                'lat'         => $r['lat'] ?: null,
                'lon'         => $r['lon'] ?: null,
                'url'         => $r['url'],
            ];
        }, $rows);

        DB::table('districts')->insertOrIgnore($data);
    }
}

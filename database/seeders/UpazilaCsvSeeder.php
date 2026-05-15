<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpazilaCsvSeeder extends Seeder
{
    private const COLUMNS = ['id', 'district_id', 'name', 'bn_name', 'url'];

    public function run(): void
    {
        $rows = array_map('str_getcsv', file(database_path('seeders/csv/upazilas.csv')));

        $data = array_map(fn ($row) => array_combine(self::COLUMNS, $row), $rows);

        DB::table('upazilas')->insertOrIgnore($data);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionCsvSeeder extends Seeder
{
    private const COLUMNS = ['id', 'name', 'bn_name', 'url'];

    public function run(): void
    {
        $rows = array_map('str_getcsv', file(database_path('seeders/csv/divisions.csv')));

        $data = array_map(fn ($row) => array_combine(self::COLUMNS, $row), $rows);

        DB::table('divisions')->insertOrIgnore($data);
    }
}

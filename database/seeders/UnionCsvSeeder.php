<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnionCsvSeeder extends Seeder
{
    private const COLUMNS = ['id', 'upazilla_id', 'name', 'bn_name', 'url'];

    public function run(): void
    {
        $rows = array_map('str_getcsv', file(database_path('seeders/csv/unions.csv')));

        // Chunk inserts to avoid hitting MySQL max_allowed_packet on large datasets
        foreach (array_chunk($rows, 500) as $chunk) {
            $data = array_map(fn ($row) => array_combine(self::COLUMNS, $row), $chunk);
            DB::table('unions')->insertOrIgnore($data);
        }
    }
}

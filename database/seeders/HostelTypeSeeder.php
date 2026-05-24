<?php

namespace Database\Seeders;

use App\Models\HostelType;
use Illuminate\Database\Seeder;

class HostelTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'student',   'label' => 'Student'],
            ['name' => 'women',     'label' => "Women's"],
            ['name' => 'mess',      'label' => 'Bachelor Mess'],
            ['name' => 'corporate', 'label' => 'Corporate'],
            ['name' => 'worker',    'label' => "Worker's"],
            ['name' => 'medical',   'label' => 'Medical'],
        ];

        foreach ($types as $type) {
            HostelType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}

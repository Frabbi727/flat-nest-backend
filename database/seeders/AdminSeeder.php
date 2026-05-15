<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@flatnest.com'],
            [
                'name'          => 'Admin',
                'password_hash' => Hash::make('admin1234'),
                'phone'         => '01700000000',
                'role'          => 'admin',
                'is_complete'   => true,
            ]
        );
    }
}

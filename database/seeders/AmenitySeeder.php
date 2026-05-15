<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    private const AMENITIES = [
        ['name' => 'wifi',        'label' => 'WiFi'],
        ['name' => 'ac',          'label' => 'Air Conditioning'],
        ['name' => 'parking',     'label' => 'Parking'],
        ['name' => 'lift',        'label' => 'Elevator / Lift'],
        ['name' => 'generator',   'label' => 'Generator'],
        ['name' => 'gas',         'label' => 'Gas Line'],
        ['name' => 'water',       'label' => 'Water Supply'],
        ['name' => 'security',    'label' => 'Security Guard'],
        ['name' => 'cctv',        'label' => 'CCTV'],
        ['name' => 'furnished',   'label' => 'Furnished'],
        ['name' => 'balcony',     'label' => 'Balcony'],
        ['name' => 'rooftop',     'label' => 'Rooftop Access'],
        ['name' => 'gym',         'label' => 'Gym'],
        ['name' => 'swimming',    'label' => 'Swimming Pool'],
        ['name' => 'intercom',    'label' => 'Intercom'],
    ];

    public function run(): void
    {
        Amenity::upsert(self::AMENITIES, uniqueBy: ['name'], update: ['label']);
    }
}
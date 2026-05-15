<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Models\Amenity;
use App\Models\Listing;
use App\Models\ListingType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'owner@flatnest.com'],
            [
                'name'          => 'Test Owner',
                'password_hash' => Hash::make('password'),
                'phone'         => '01711111111',
                'role'          => 'owner',
                'is_complete'   => true,
            ]
        );

        // Resolve amenity and listing type IDs
        $amenityIds = Amenity::whereIn('name', ['wifi', 'ac', 'parking', 'lift', 'generator', 'gas', 'gym'])
            ->pluck('id', 'name');

        $typeIds = ListingType::pluck('id', 'name');

        $listings = [
            ['title' => '3BHK Family Flat – Dhanmondi',    'area' => 'Dhanmondi',   'type' => 'Family',   'price' => 35000, 'beds' => 3, 'baths' => 2, 'amenities' => ['wifi', 'gas', 'lift']],
            ['title' => 'Bachelor Flat – Mirpur 10',        'area' => 'Mirpur',      'type' => 'Bachelor', 'price' => 12000, 'beds' => 2, 'baths' => 1, 'amenities' => ['wifi', 'parking']],
            ['title' => 'Student Room – Mohammadpur',       'area' => 'Mohammadpur', 'type' => 'Student',  'price' => 6000,  'beds' => 1, 'baths' => 1, 'amenities' => ['wifi']],
            ['title' => 'Couple Flat – Gulshan 2',          'area' => 'Gulshan',     'type' => 'Couple',   'price' => 28000, 'beds' => 2, 'baths' => 2, 'amenities' => ['wifi', 'generator', 'gym']],
            ['title' => 'Sublet Room – Banani',             'area' => 'Banani',      'type' => 'Sublet',   'price' => 8000,  'beds' => 1, 'baths' => 1, 'amenities' => ['wifi', 'gas']],
            ['title' => 'Family Flat – Uttara Sector 7',   'area' => 'Uttara',      'type' => 'Family',   'price' => 22000, 'beds' => 3, 'baths' => 2, 'amenities' => ['parking', 'generator']],
            ['title' => 'Bachelor Room – Farmgate',         'area' => 'Farmgate',    'type' => 'Bachelor', 'price' => 9000,  'beds' => 1, 'baths' => 1, 'amenities' => ['wifi']],
            ['title' => '2BHK – Bashundhara R/A',           'area' => 'Bashundhara', 'type' => 'Family',   'price' => 26000, 'beds' => 2, 'baths' => 2, 'amenities' => ['wifi', 'gas', 'lift', 'parking']],
            ['title' => 'Student Hostel Room – Azimpur',    'area' => 'Azimpur',     'type' => 'Student',  'price' => 5000,  'beds' => 1, 'baths' => 1, 'amenities' => []],
            ['title' => 'Luxury Flat – Baridhara Diplomat', 'area' => 'Baridhara',   'type' => 'Family',   'price' => 80000, 'beds' => 4, 'baths' => 3, 'amenities' => ['wifi', 'gas', 'lift', 'parking', 'generator', 'gym']],
        ];

        foreach ($listings as $data) {
            $listing = Listing::create([
                'owner_id'        => $owner->id,
                'listing_type_id' => $typeIds[$data['type']] ?? null,
                'title'           => $data['title'],
                'area'            => $data['area'],
                'price'           => $data['price'],
                'beds'            => $data['beds'],
                'baths'           => $data['baths'],
                'status'          => ListingStatus::Active,
                'description'     => 'A great place to stay in ' . $data['area'] . '.',
            ]);

            $syncIds = collect($data['amenities'])->map(fn ($slug) => $amenityIds[$slug] ?? null)->filter()->values()->all();
            if ($syncIds) {
                $listing->amenities()->sync($syncIds);
            }
        }
    }
}
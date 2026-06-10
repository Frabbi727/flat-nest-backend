<?php

namespace Tests\Feature;

use App\Contracts\Repositories\ListingRepositoryInterface;
use App\Enums\ListingStatus;
use App\Models\District;
use App\Models\Listing;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\User;
use App\Models\Division;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingRepositorySearchTest extends TestCase
{
    use RefreshDatabase;

    private ListingRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(ListingRepositoryInterface::class);
    }

    public function test_search_by_title()
    {
        $owner = User::factory()->create();
        Listing::create([
            'owner_id' => $owner->id,
            'title' => 'Unique Title X',
            'price' => 1000,
            'beds' => 3,
            'baths' => 2,
            'area' => 'Dhaka',
            'status' => ListingStatus::Active,
        ]);
        Listing::create([
            'owner_id' => $owner->id,
            'title' => 'Something else',
            'price' => 1000,
            'beds' => 3,
            'baths' => 2,
            'area' => 'Dhaka',
            'status' => ListingStatus::Active,
        ]);

        $results = $this->repository->findActive(['search' => 'Unique Title X']);
        $this->assertCount(1, $results);
        $this->assertEquals('Unique Title X', $results->first()->title);
    }

    public function test_search_by_description()
    {
        $owner = User::factory()->create();
        Listing::create([
            'owner_id' => $owner->id,
            'title' => 'Listing 1',
            'description' => 'This is a special keyword description',
            'price' => 1000,
            'beds' => 3,
            'baths' => 2,
            'area' => 'Dhaka',
            'status' => ListingStatus::Active,
        ]);

        $results = $this->repository->findActive(['search' => 'special keyword']);
        $this->assertCount(1, $results);
    }

    public function test_search_by_address_fields()
    {
        $owner = User::factory()->create();
        Listing::create([
            'owner_id' => $owner->id,
            'title' => 'Listing 1',
            'road_and_house' => 'House 12, Road 5',
            'price' => 1000,
            'beds' => 3,
            'baths' => 2,
            'area' => 'Dhaka',
            'status' => ListingStatus::Active,
        ]);

        $results = $this->repository->findActive(['search' => 'House 12']);
        $this->assertCount(1, $results);
    }

    public function test_search_by_district_name()
    {
        $division = Division::create(['name' => 'Dhaka', 'bn_name' => 'ঢাকা', 'url' => 'dhaka.gov.bd']);
        $district = District::create(['division_id' => $division->id, 'name' => 'Gazipur', 'bn_name' => 'গাজীপুর', 'url' => 'gazipur.gov.bd']);
        $owner = User::factory()->create();
        
        Listing::create([
            'owner_id' => $owner->id,
            'title' => 'Listing in Gazipur',
            'district_id' => $district->id,
            'price' => 1000,
            'beds' => 3,
            'baths' => 2,
            'area' => 'Dhaka',
            'status' => ListingStatus::Active,
        ]);

        $results = $this->repository->findActive(['search' => 'Gazipur']);
        $this->assertCount(1, $results);
        
        $resultsBn = $this->repository->findActive(['search' => 'গাজীপুর']);
        $this->assertCount(1, $resultsBn);
    }

    public function test_search_by_upazila_name()
    {
        $division = Division::create(['name' => 'Dhaka', 'bn_name' => 'ঢাকা', 'url' => 'dhaka.gov.bd']);
        $district = District::create(['division_id' => $division->id, 'name' => 'Dhaka', 'bn_name' => 'ঢাকা', 'url' => 'dhaka.gov.bd']);
        $upazila = Upazila::create(['district_id' => $district->id, 'name' => 'Savar', 'bn_name' => 'সাভার', 'url' => 'savar.gov.bd']);
        $owner = User::factory()->create();
        
        Listing::create([
            'owner_id' => $owner->id,
            'title' => 'Listing in Savar',
            'upazila_id' => $upazila->id,
            'price' => 1000,
            'beds' => 3,
            'baths' => 2,
            'area' => 'Dhaka',
            'status' => ListingStatus::Active,
        ]);

        $results = $this->repository->findActive(['search' => 'Savar']);
        $this->assertCount(1, $results);
        
        $resultsBn = $this->repository->findActive(['search' => 'সাভার']);
        $this->assertCount(1, $resultsBn);
    }

    public function test_search_by_union_name()
    {
        $division = Division::create(['name' => 'Dhaka', 'bn_name' => 'ঢাকা', 'url' => 'dhaka.gov.bd']);
        $district = District::create(['division_id' => $division->id, 'name' => 'Dhaka', 'bn_name' => 'ঢাকা', 'url' => 'dhaka.gov.bd']);
        $upazila = Upazila::create(['district_id' => $district->id, 'name' => 'Savar', 'bn_name' => 'সাভার', 'url' => 'savar.gov.bd']);
        $union = Union::create(['upazilla_id' => $upazila->id, 'name' => 'Amin Bazar', 'bn_name' => 'আমিন বাজার', 'url' => 'aminbazar.gov.bd']);
        $owner = User::factory()->create();
        
        Listing::create([
            'owner_id' => $owner->id,
            'title' => 'Listing in Amin Bazar',
            'union_id' => $union->id,
            'price' => 1000,
            'beds' => 3,
            'baths' => 2,
            'area' => 'Dhaka',
            'status' => ListingStatus::Active,
        ]);

        $results = $this->repository->findActive(['search' => 'Amin Bazar']);
        $this->assertCount(1, $results);
        
        $resultsBn = $this->repository->findActive(['search' => 'আমিন বাজার']);
        $this->assertCount(1, $resultsBn);
    }
}

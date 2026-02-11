<?php

namespace Tests\Feature\Api\V1;

use App\Features\Cities\Models\City;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelPricesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_latest_fuel_prices_for_city_and_type(): void
    {
        $this->seed(DatabaseSeeder::class);
        $prishtineId = City::query()->where('slug', 'prishtine')->value('id');

        $response = $this->getJson("/api/v1/fuel-prices?city_id={$prishtineId}&fuel_type=diesel");

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.fuel_type', 'diesel')
            ->assertJsonPath('data.0.recorded_at', '2026-02-10')
            ->assertJsonPath('data.0.city.slug', 'prishtine');
    }
}

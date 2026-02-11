<?php

namespace Tests\Feature\Api\V1;

use App\Features\Cities\Models\City;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelLatestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_latest_fuel_data_for_city(): void
    {
        $this->seed(DatabaseSeeder::class);
        $prishtineId = City::query()->where('slug', 'prishtine')->value('id');

        $response = $this->getJson("/api/v1/fuel/latest?city_id={$prishtineId}");

        $response
            ->assertOk()
            ->assertJsonCount(8, 'data')
            ->assertJsonPath('data.0.city.slug', 'prishtine')
            ->assertJsonPath('data.0.recorded_at', '2026-02-10');
    }
}

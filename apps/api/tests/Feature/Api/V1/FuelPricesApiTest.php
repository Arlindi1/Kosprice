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

        $response->assertOk();

        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(3, count($data));
        $this->assertTrue(
            collect($data)->every(
                static fn (array $row): bool => ($row['fuel_type'] ?? null) === 'diesel'
                    && ($row['city']['slug'] ?? null) === 'prishtine'
                    && !empty($row['brand_key'] ?? null)
                    && isset($row['price_eur_per_l'])
            )
        );
        $this->assertNotNull($data[0]['recorded_at'] ?? null);
    }
}

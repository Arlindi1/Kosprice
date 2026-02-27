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

        $response = $this->getJson("/api/v1/fuel/latest?city_id={$prishtineId}&type=diesel");

        $response->assertOk();

        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(8, count($data));
        $this->assertTrue(
            collect($data)->every(
                static fn (array $row): bool => ($row['city']['slug'] ?? null) === 'prishtine'
                    && ($row['fuel_type'] ?? null) === 'diesel'
                    && !empty($row['brand_key'] ?? null)
            )
        );
        $this->assertNotNull($data[0]['recorded_at'] ?? null);
    }
}

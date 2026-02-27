<?php

namespace Tests\Feature\Api\V1;

use App\Features\Cities\Models\City;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelStationsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_ranked_stations_for_city_and_type(): void
    {
        $this->seed(DatabaseSeeder::class);
        $prishtineId = City::query()->where('slug', 'prishtine')->value('id');

        $response = $this->getJson("/api/v1/fuel/stations?city_id={$prishtineId}&type=petrol95");

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'station_id',
                        'brand_key',
                        'brand_name',
                        'station_name',
                        'address',
                        'fuel_type',
                        'city_id',
                        'city_name',
                        'city_slug',
                        'price_eur_per_l',
                        'recorded_at',
                    ],
                ],
                'meta' => ['city_id', 'type', 'count', 'updated_at'],
            ])
            ->assertJsonPath('meta.city_id', $prishtineId)
            ->assertJsonPath('meta.type', 'petrol95');

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(8, count($data));
        $this->assertTrue(
            collect($data)->every(
                static fn (array $row): bool => ($row['city_slug'] ?? null) === 'prishtine'
                    && ($row['fuel_type'] ?? null) === 'petrol95'
                    && (float) ($row['price_eur_per_l'] ?? 0) > 0
            )
        );

        for ($index = 1; $index < count($data); $index++) {
            $this->assertGreaterThanOrEqual(
                (float) $data[$index - 1]['price_eur_per_l'],
                (float) $data[$index]['price_eur_per_l']
            );
        }
    }
}


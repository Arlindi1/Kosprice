<?php

namespace Tests\Feature\Api\V1;

use App\Features\Cities\Models\City;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelBrandsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_brand_summary_for_city_and_type(): void
    {
        $this->seed(DatabaseSeeder::class);
        $prishtineId = City::query()->where('slug', 'prishtine')->value('id');

        $response = $this->getJson("/api/v1/fuel/brands?city_id={$prishtineId}&type=diesel");

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'brand_key',
                        'brand_name',
                        'best_price',
                        'best_station_name',
                        'station_count',
                        'updated_at',
                    ],
                ],
                'meta' => ['city_id', 'type', 'count'],
            ])
            ->assertJsonPath('meta.city_id', $prishtineId)
            ->assertJsonPath('meta.type', 'diesel');

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(4, count($data));
        $this->assertTrue(
            collect($data)->every(
                static fn (array $row): bool => (int) ($row['station_count'] ?? 0) >= 2
                    && !empty($row['best_station_name'] ?? null)
                    && (float) ($row['best_price'] ?? 0) > 0
            )
        );
    }
}


<?php

namespace Tests\Feature\Api\V1;

use App\Features\Cities\Models\City;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelHistoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_history_by_brand_key_when_requested(): void
    {
        $this->seed(DatabaseSeeder::class);
        $prishtineId = City::query()->where('slug', 'prishtine')->value('id');

        $response = $this->getJson(
            "/api/v1/fuel/history?city_id={$prishtineId}&type=diesel&brand_key=shell&days=14"
        );

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'recorded_at',
                        'fuel_type',
                        'avg_price_eur_liter',
                    ],
                ],
                'meta' => [
                    'city_id',
                    'type',
                    'brand_key',
                    'days',
                    'start_date',
                    'end_date',
                    'count',
                ],
            ])
            ->assertJsonPath('meta.city_id', $prishtineId)
            ->assertJsonPath('meta.type', 'diesel')
            ->assertJsonPath('meta.brand_key', 'shell')
            ->assertJsonPath('meta.days', 14);

        $this->assertGreaterThan(0, count($response->json('data')));
    }
}


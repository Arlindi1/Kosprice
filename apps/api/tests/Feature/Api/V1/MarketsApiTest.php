<?php

namespace Tests\Feature\Api\V1;

use App\Features\Cities\Models\City;
use App\Features\Markets\Models\Market;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_markets_for_selected_city(): void
    {
        $this->seed(DatabaseSeeder::class);
        $prishtineId = City::query()->where('slug', 'prishtine')->value('id');

        $response = $this->getJson("/api/v1/markets?city_id={$prishtineId}");

        $response->assertOk();

        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(3, count($data));
        $this->assertTrue(
            collect($data)->every(
                static fn (array $market): bool => ($market['city']['slug'] ?? null) === 'prishtine'
            )
        );
    }

    public function test_it_returns_latest_market_basket_with_total(): void
    {
        $this->seed(DatabaseSeeder::class);
        $prishtineId = City::query()->where('slug', 'prishtine')->value('id');
        $marketId = Market::query()->where('city_id', $prishtineId)->value('id');

        $response = $this->getJson("/api/v1/markets/{$marketId}/basket");

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'market' => ['id', 'name', 'address', 'city'],
                    'recorded_at',
                    'total_price_eur',
                    'items',
                ],
            ]);

        $this->assertNotNull($response->json('data.recorded_at'));
        $this->assertGreaterThan(0, (float) $response->json('data.total_price_eur'));
        $this->assertGreaterThanOrEqual(20, count($response->json('data.items')));
    }
}

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

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.city.slug', 'prishtine')
            ->assertJsonPath('data.1.city.slug', 'prishtine');
    }

    public function test_it_returns_latest_market_basket_with_total(): void
    {
        $this->seed(DatabaseSeeder::class);
        $marketId = Market::query()->where('name', 'ETC Prishtine')->value('id');

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
            ])
            ->assertJsonPath('data.recorded_at', '2026-02-10')
            ->assertJsonPath('data.total_price_eur', 12.58)
            ->assertJsonCount(6, 'data.items');
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Features\Cities\Models\City;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasketApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_cheapest_basket_for_city(): void
    {
        $this->seed(DatabaseSeeder::class);
        $prishtineId = City::query()->where('slug', 'prishtine')->value('id');

        $response = $this->getJson("/api/v1/basket/cheapest?city_id={$prishtineId}");

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'market' => ['id', 'name'],
                    'city' => ['id', 'name', 'slug'],
                    'recorded_at',
                    'total_price_eur',
                ],
            ])
            ->assertJsonPath('data.city.slug', 'prishtine')
            ->assertJsonPath('data.market.name', 'ETC Prishtine')
            ->assertJsonPath('data.recorded_at', '2026-02-10')
            ->assertJsonPath('data.total_price_eur', 12.58);
    }
}

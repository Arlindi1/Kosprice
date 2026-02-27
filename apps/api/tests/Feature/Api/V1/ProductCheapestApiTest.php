<?php

namespace Tests\Feature\Api\V1;

use App\Features\Cities\Models\City;
use App\Features\Markets\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCheapestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_cheapest_market_and_alternatives_for_product_in_city(): void
    {
        $this->seed(DatabaseSeeder::class);
        $cityId = City::query()->where('slug', 'prishtine')->value('id');
        $productId = Product::query()->where('name', 'Buke Integrale')->value('id');

        $response = $this->getJson("/api/v1/products/{$productId}/cheapest?city_id={$cityId}");

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'product' => ['id', 'name', 'brand', 'variant', 'category', 'image_key', 'unit_label', 'is_core_basket'],
                    'city' => ['id', 'name', 'slug'],
                    'recorded_at',
                    'cheapest' => [
                        'market' => ['id', 'name', 'address'],
                        'price_eur',
                        'delta_from_cheapest_eur',
                    ],
                    'alternatives' => [
                        '*' => [
                            'market' => ['id', 'name', 'address'],
                            'price_eur',
                            'delta_from_cheapest_eur',
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('data.city.id', $cityId)
            ->assertJsonPath('data.city.slug', 'prishtine')
            ->assertJsonPath('data.product.id', $productId)
            ->assertJsonPath('data.product.name', 'Buke Integrale');

        $this->assertNotNull($response->json('data.recorded_at'));
        $this->assertNotNull($response->json('data.cheapest.market.name'));
        $this->assertGreaterThan(0, (float) $response->json('data.cheapest.price_eur'));
        $this->assertGreaterThanOrEqual(2, count($response->json('data.alternatives')));
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Features\Cities\Models\City;
use App\Features\Markets\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPricesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_ranked_market_prices_for_product_in_city(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cityId = City::query()->where('slug', 'prishtine')->value('id');
        $productId = Product::query()->where('name', 'Buke Integrale')->value('id');

        $response = $this->getJson("/api/v1/products/{$productId}/prices?city_id={$cityId}");

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'product' => [
                        'id',
                        'name',
                        'brand',
                        'variant',
                        'unit_label',
                        'category',
                        'image_key',
                    ],
                    'city' => ['id', 'name', 'slug'],
                    'prices' => [
                        '*' => [
                            'market_id',
                            'market_name',
                            'address',
                            'price_eur',
                            'recorded_at',
                        ],
                    ],
                ],
                'meta' => [
                    'count',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.city.id', $cityId)
            ->assertJsonPath('data.product.id', $productId)
            ->assertJsonPath('meta.count', count($response->json('data.prices')));

        $prices = $response->json('data.prices');
        $this->assertGreaterThan(0, count($prices));

        for ($index = 1; $index < count($prices); $index++) {
            $this->assertGreaterThanOrEqual(
                (float) $prices[$index - 1]['price_eur'],
                (float) $prices[$index]['price_eur']
            );
        }
    }
}

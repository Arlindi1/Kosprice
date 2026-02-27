<?php

namespace Tests\Feature\Api\V1;

use App\Features\Cities\Models\City;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_products_by_category_with_meta(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->getJson('/api/v1/products?category=dairy');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'brand',
                        'variant',
                        'category',
                        'unit',
                        'image_key',
                        'unit_label',
                        'brand_hint',
                        'is_core_basket',
                    ],
                ],
                'meta' => ['count', 'category'],
            ])
            ->assertJsonPath('meta.category', 'dairy');

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
        $this->assertSame(count($response->json('data')), $response->json('meta.count'));
    }

    public function test_it_returns_product_catalog_with_city_cheapest_prices(): void
    {
        $this->seed(DatabaseSeeder::class);
        $prishtineId = City::query()->where('slug', 'prishtine')->value('id');

        $response = $this->getJson("/api/v1/products/catalog?city_id={$prishtineId}");

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'brand',
                        'variant',
                        'category',
                        'image_key',
                        'unit_label',
                        'is_core_basket',
                        'cheapest_price_today',
                        'cheapest_market_name',
                    ],
                ],
                'meta' => ['count', 'city_id', 'recorded_at'],
            ])
            ->assertJsonPath('meta.city_id', $prishtineId);

        $this->assertNotNull($response->json('meta.recorded_at'));
        $this->assertGreaterThanOrEqual(20, count($response->json('data')));
        $this->assertSame(count($response->json('data')), $response->json('meta.count'));

        $bread = collect($response->json('data'))->firstWhere('image_key', 'bread');

        $this->assertNotNull($bread);
        $this->assertSame('Buke Integrale', $bread['name']);
        $this->assertIsString($bread['cheapest_market_name']);
        $this->assertGreaterThan(0, (float) $bread['cheapest_price_today']);
    }
}

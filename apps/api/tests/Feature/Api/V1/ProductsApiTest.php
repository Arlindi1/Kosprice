<?php

namespace Tests\Feature\Api\V1;

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
                    '*' => ['id', 'name', 'category', 'unit'],
                ],
                'meta' => ['count', 'category'],
            ])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.count', 2)
            ->assertJsonPath('meta.category', 'dairy');
    }
}

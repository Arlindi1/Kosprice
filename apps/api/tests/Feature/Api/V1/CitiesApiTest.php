<?php

namespace Tests\Feature\Api\V1;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitiesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_cities_with_consistent_shape(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->getJson('/api/v1/cities');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug'],
                ],
            ])
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.name', 'Gjilan');
    }
}

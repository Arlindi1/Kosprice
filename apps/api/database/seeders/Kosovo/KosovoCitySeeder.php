<?php

namespace Database\Seeders\Kosovo;

use App\Features\Cities\Models\City;
use Illuminate\Database\Seeder;

class KosovoCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Prishtine', 'slug' => 'prishtine'],
            ['name' => 'Prizren', 'slug' => 'prizren'],
            ['name' => 'Peje', 'slug' => 'peje'],
            ['name' => 'Gjilan', 'slug' => 'gjilan'],
        ];

        foreach ($cities as $city) {
            City::query()->updateOrCreate(
                ['slug' => $city['slug']],
                ['name' => $city['name']]
            );
        }
    }
}

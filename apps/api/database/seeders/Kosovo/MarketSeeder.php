<?php

namespace Database\Seeders\Kosovo;

use App\Features\Cities\Models\City;
use App\Features\Markets\Models\Market;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cityIds = City::query()->pluck('id', 'slug');

        $markets = [
            ['city_slug' => 'prishtine', 'name' => 'ETC Prishtine', 'address' => 'Bulevardi Bill Clinton, Prishtine'],
            ['city_slug' => 'prishtine', 'name' => 'Viva Fresh Prishtine', 'address' => 'Rruga B, Prishtine'],
            ['city_slug' => 'prizren', 'name' => 'Interex Prizren', 'address' => 'Rruga Tirana, Prizren'],
            ['city_slug' => 'peje', 'name' => 'Elkos Market Peje', 'address' => 'Rruga Mbreteresha Teute, Peje'],
            ['city_slug' => 'gjilan', 'name' => 'Meridian Express Gjilan', 'address' => 'Rruga Abdullah Tahiri, Gjilan'],
        ];

        foreach ($markets as $market) {
            Market::query()->updateOrCreate(
                ['name' => $market['name']],
                [
                    'city_id' => $cityIds[$market['city_slug']],
                    'address' => $market['address'],
                ]
            );
        }
    }
}

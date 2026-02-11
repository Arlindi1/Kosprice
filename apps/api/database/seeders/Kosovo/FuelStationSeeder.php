<?php

namespace Database\Seeders\Kosovo;

use App\Features\Cities\Models\City;
use App\Features\Fuel\Models\FuelStation;
use Illuminate\Database\Seeder;

class FuelStationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cityIds = City::query()->pluck('id', 'slug');

        $stations = [
            ['city_slug' => 'prishtine', 'name' => 'Shell Prishtine', 'address' => 'Veternik, Prishtine'],
            ['city_slug' => 'prishtine', 'name' => 'HIB Petroll Prishtine', 'address' => 'Dardani, Prishtine'],
            ['city_slug' => 'prizren', 'name' => 'Shell Prizren', 'address' => 'Rruga Transit, Prizren'],
            ['city_slug' => 'peje', 'name' => 'IP Petrol Peje', 'address' => 'Rruga Dukagjini, Peje'],
            ['city_slug' => 'gjilan', 'name' => 'HIB Petroll Gjilan', 'address' => 'Rruga e Kumanoves, Gjilan'],
        ];

        foreach ($stations as $station) {
            FuelStation::query()->updateOrCreate(
                ['name' => $station['name']],
                [
                    'city_id' => $cityIds[$station['city_slug']],
                    'address' => $station['address'],
                ]
            );
        }
    }
}

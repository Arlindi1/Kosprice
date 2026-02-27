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

        $marketsByCity = [
            'prishtine' => [
                ['name' => 'Maxi Prishtine Center', 'address' => 'Bulevardi Bill Clinton 12, Prishtine'],
                ['name' => 'Meridian Express Ulpiana', 'address' => 'Rruga Rexhep Luci 5, Prishtine'],
                ['name' => 'Viva Fresh Bregu i Diellit', 'address' => 'Rruga B 72, Prishtine'],
                ['name' => 'ETC Veternik', 'address' => 'Lagjja Veternik, Prishtine'],
            ],
            'prizren' => [
                ['name' => 'Maxi Prizren Center', 'address' => 'Rruga Tirana 24, Prizren'],
                ['name' => 'Meridian Express Ortakoll', 'address' => 'Rruga Adem Jashari 18, Prizren'],
                ['name' => 'Viva Fresh Bazhdarhane', 'address' => 'Rruga Marin Barleti 9, Prizren'],
                ['name' => 'Interex Prizren Transit', 'address' => 'Zona Transit, Prizren'],
            ],
            'peje' => [
                ['name' => 'Maxi Peje Qender', 'address' => 'Rruga Mbreteresha Teute 33, Peje'],
                ['name' => 'Meridian Express Peje', 'address' => 'Rruga Adem Jashari 11, Peje'],
                ['name' => 'Viva Fresh Kapeshnice', 'address' => 'Lagjja Kapeshnice, Peje'],
                ['name' => 'ETC Peje Dukagjini', 'address' => 'Rruga Dukagjini 44, Peje'],
            ],
            'gjilan' => [
                ['name' => 'Maxi Gjilan Center', 'address' => 'Rruga Idriz Seferi 19, Gjilan'],
                ['name' => 'Meridian Express Dardania', 'address' => 'Lagjja Dardania, Gjilan'],
                ['name' => 'Viva Fresh Gjilan', 'address' => 'Rruga Abdullah Tahiri 15, Gjilan'],
                ['name' => 'Interex Gjilan Arbri', 'address' => 'Rruga Arbri, Gjilan'],
            ],
        ];

        foreach ($marketsByCity as $citySlug => $markets) {
            $cityId = $cityIds[$citySlug] ?? null;

            if ($cityId === null) {
                continue;
            }

            foreach ($markets as $market) {
                Market::query()->updateOrCreate(
                    ['name' => $market['name']],
                    [
                        'city_id' => $cityId,
                        'address' => $market['address'],
                    ]
                );
            }
        }
    }
}

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

        $stationsByCity = [
            'prishtine' => [
                ['brand_key' => 'shell', 'name' => 'Shell Prishtine Veternik', 'address' => 'Lagjja Veternik, Prishtine'],
                ['brand_key' => 'shell', 'name' => 'Shell Prishtine Dardani', 'address' => 'Rruga Tirana 12, Prishtine'],
                ['brand_key' => 'hib', 'name' => 'HIB Prishtine Qender', 'address' => 'Rruga Agim Ramadani 45, Prishtine'],
                ['brand_key' => 'hib', 'name' => 'HIB Prishtine Dragodan', 'address' => 'Rruga Ahmet Krasniqi 9, Prishtine'],
                ['brand_key' => 'alpetrol', 'name' => 'Al Petrol Prishtine Emshir', 'address' => 'Rruga Fehmi Agani 18, Prishtine'],
                ['brand_key' => 'alpetrol', 'name' => 'Al Petrol Prishtine Kalabri', 'address' => 'Rruga e Ulpianes 37, Prishtine'],
                ['brand_key' => 'ippetrol', 'name' => 'IP Petrol Prishtine Arbri', 'address' => 'Rruga B 74, Prishtine'],
                ['brand_key' => 'ippetrol', 'name' => 'IP Petrol Prishtine Lakrishte', 'address' => 'Rruga Rexhep Mala 7, Prishtine'],
                ['brand_key' => 'liriaoil', 'name' => 'Liria Oil Prishtine Bregu', 'address' => 'Bregu i Diellit, Prishtine'],
                ['brand_key' => 'liriaoil', 'name' => 'Liria Oil Prishtine Mati', 'address' => 'Mati 1, Prishtine'],
            ],
            'prizren' => [
                ['brand_key' => 'shell', 'name' => 'Shell Prizren Transit', 'address' => 'Zona Transit, Prizren'],
                ['brand_key' => 'shell', 'name' => 'Shell Prizren Ortakoll', 'address' => 'Rruga Remzi Ademaj 23, Prizren'],
                ['brand_key' => 'hib', 'name' => 'HIB Prizren Qender', 'address' => 'Rruga Tirana 41, Prizren'],
                ['brand_key' => 'hib', 'name' => 'HIB Prizren Bazhdarhane', 'address' => 'Rruga Marin Barleti 16, Prizren'],
                ['brand_key' => 'alpetrol', 'name' => 'Al Petrol Prizren Arbane', 'address' => 'Rruga Adem Jashari 74, Prizren'],
                ['brand_key' => 'alpetrol', 'name' => 'Al Petrol Prizren Lakuriq', 'address' => 'Rruga Ismet Jashari 11, Prizren'],
                ['brand_key' => 'ippetrol', 'name' => 'IP Petrol Prizren Shadervan', 'address' => 'Rruga Saraqi 8, Prizren'],
                ['brand_key' => 'ippetrol', 'name' => 'IP Petrol Prizren Vlashnje', 'address' => 'Rruga Vlashnje, Prizren'],
                ['brand_key' => 'liriaoil', 'name' => 'Liria Oil Prizren Qafe', 'address' => 'Qafa e Pazarit, Prizren'],
                ['brand_key' => 'liriaoil', 'name' => 'Liria Oil Prizren Arbane', 'address' => 'Rruga Ilir Konushevci 31, Prizren'],
            ],
            'peje' => [
                ['brand_key' => 'shell', 'name' => 'Shell Peje Qender', 'address' => 'Rruga Mbreteresha Teute 41, Peje'],
                ['brand_key' => 'shell', 'name' => 'Shell Peje Kapeshnice', 'address' => 'Lagjja Kapeshnice, Peje'],
                ['brand_key' => 'hib', 'name' => 'HIB Peje Qendra', 'address' => 'Rruga Skenderbeu 24, Peje'],
                ['brand_key' => 'hib', 'name' => 'HIB Peje Kodra e Trimave', 'address' => 'Rruga Zahir Pajaziti 9, Peje'],
                ['brand_key' => 'alpetrol', 'name' => 'Al Petrol Peje Dukagjini', 'address' => 'Rruga Dukagjini 61, Peje'],
                ['brand_key' => 'alpetrol', 'name' => 'Al Petrol Peje Vitomirice', 'address' => 'Rruga Vitomirice, Peje'],
                ['brand_key' => 'ippetrol', 'name' => 'IP Petrol Peje Fidanishte', 'address' => 'Rruga 28 Nentori 19, Peje'],
                ['brand_key' => 'ippetrol', 'name' => 'IP Petrol Peje Bellopoje', 'address' => 'Rruga Bellopoje 5, Peje'],
                ['brand_key' => 'liriaoil', 'name' => 'Liria Oil Peje Dardania', 'address' => 'Lagjja Dardania, Peje'],
                ['brand_key' => 'liriaoil', 'name' => 'Liria Oil Peje Qender', 'address' => 'Rruga Haxhi Zeka 10, Peje'],
            ],
            'gjilan' => [
                ['brand_key' => 'shell', 'name' => 'Shell Gjilan Qender', 'address' => 'Rruga Idriz Seferi 55, Gjilan'],
                ['brand_key' => 'shell', 'name' => 'Shell Gjilan Dardania', 'address' => 'Lagjja Dardania, Gjilan'],
                ['brand_key' => 'hib', 'name' => 'HIB Gjilan Qender', 'address' => 'Rruga e Kumanoves 13, Gjilan'],
                ['brand_key' => 'hib', 'name' => 'HIB Gjilan Arbri', 'address' => 'Rruga Arberi 27, Gjilan'],
                ['brand_key' => 'alpetrol', 'name' => 'Al Petrol Gjilan Abdullah Tahiri', 'address' => 'Rruga Abdullah Tahiri 88, Gjilan'],
                ['brand_key' => 'alpetrol', 'name' => 'Al Petrol Gjilan Zabeli', 'address' => 'Rruga e Zabelit 6, Gjilan'],
                ['brand_key' => 'ippetrol', 'name' => 'IP Petrol Gjilan Llashtice', 'address' => 'Rruga Llashtice, Gjilan'],
                ['brand_key' => 'ippetrol', 'name' => 'IP Petrol Gjilan Kamenice', 'address' => 'Rruga Kamenice 14, Gjilan'],
                ['brand_key' => 'liriaoil', 'name' => 'Liria Oil Gjilan Qendra', 'address' => 'Rruga Nazim Hikmet 3, Gjilan'],
                ['brand_key' => 'liriaoil', 'name' => 'Liria Oil Gjilan Dheu i Bardhe', 'address' => 'Lagjja Dheu i Bardhe, Gjilan'],
            ],
        ];

        foreach ($stationsByCity as $citySlug => $stations) {
            $cityId = $cityIds[$citySlug] ?? null;

            if ($cityId === null) {
                continue;
            }

            foreach ($stations as $station) {
                FuelStation::query()->updateOrCreate(
                    ['name' => $station['name']],
                    [
                        'city_id' => $cityId,
                        'brand_key' => $station['brand_key'],
                        'address' => $station['address'],
                    ]
                );
            }
        }
    }
}

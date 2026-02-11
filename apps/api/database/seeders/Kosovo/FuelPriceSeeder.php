<?php

namespace Database\Seeders\Kosovo;

use App\Features\Fuel\Enums\FuelType;
use App\Features\Fuel\Models\FuelPrice;
use App\Features\Fuel\Models\FuelStation;
use Illuminate\Database\Seeder;

class FuelPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stationIds = FuelStation::query()->pluck('id', 'name');

        $basePrices = [
            'Shell Prishtine' => [
                FuelType::DIESEL->value => 1.340,
                FuelType::PETROL_95->value => 1.360,
                FuelType::PETROL_98->value => 1.460,
                FuelType::LPG->value => 0.790,
            ],
            'HIB Petroll Prishtine' => [
                FuelType::DIESEL->value => 1.335,
                FuelType::PETROL_95->value => 1.355,
                FuelType::PETROL_98->value => 1.455,
                FuelType::LPG->value => 0.785,
            ],
            'Shell Prizren' => [
                FuelType::DIESEL->value => 1.332,
                FuelType::PETROL_95->value => 1.352,
                FuelType::PETROL_98->value => 1.452,
                FuelType::LPG->value => 0.782,
            ],
            'IP Petrol Peje' => [
                FuelType::DIESEL->value => 1.330,
                FuelType::PETROL_95->value => 1.350,
                FuelType::PETROL_98->value => 1.448,
                FuelType::LPG->value => 0.780,
            ],
            'HIB Petroll Gjilan' => [
                FuelType::DIESEL->value => 1.337,
                FuelType::PETROL_95->value => 1.357,
                FuelType::PETROL_98->value => 1.457,
                FuelType::LPG->value => 0.788,
            ],
        ];

        foreach ($basePrices as $stationName => $prices) {
            foreach ($prices as $fuelType => $price) {
                $stationId = $stationIds[$stationName];

                FuelPrice::query()->updateOrCreate(
                    [
                        'fuel_station_id' => $stationId,
                        'fuel_type' => $fuelType,
                        'recorded_at' => '2026-02-01',
                    ],
                    ['price_eur_liter' => $price]
                );

                FuelPrice::query()->updateOrCreate(
                    [
                        'fuel_station_id' => $stationId,
                        'fuel_type' => $fuelType,
                        'recorded_at' => '2026-02-10',
                    ],
                    ['price_eur_liter' => round($price + 0.008, 3)]
                );
            }
        }
    }
}

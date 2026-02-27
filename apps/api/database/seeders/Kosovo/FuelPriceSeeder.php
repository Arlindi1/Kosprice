<?php

namespace Database\Seeders\Kosovo;

use App\Features\Fuel\Enums\FuelType;
use App\Features\Fuel\Models\FuelPrice;
use App\Features\Fuel\Models\FuelStation;
use Carbon\CarbonImmutable;
use Database\Seeders\Kosovo\Support\SeededRng;
use Illuminate\Database\Seeder;

class FuelPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = FuelStation::query()
            ->with('city:id,slug')
            ->get(['id', 'city_id', 'brand_key', 'name']);

        if ($stations->isEmpty()) {
            return;
        }

        $rng = new SeededRng(20260212);
        $days = 30;
        $endDate = CarbonImmutable::today()->subDay();
        $startDate = $endDate->subDays($days - 1);
        $baseByType = [
            FuelType::DIESEL->value => 1.340,
            FuelType::PETROL_95->value => 1.360,
            FuelType::PETROL_98->value => 1.465,
            FuelType::LPG->value => 0.790,
        ];
        $rows = [];

        foreach ($stations as $station) {
            $citySlug = $station->city?->slug;

            if ($citySlug === null) {
                continue;
            }

            $cityMultiplier = $this->cityMultiplier($citySlug);
            $stationMultiplier = $this->stationMultiplier($station->brand_key, $rng, $station->id);

            foreach (FuelType::values() as $fuelType) {
                $basePrice = $baseByType[$fuelType];
                $volatility = $rng->float(
                    'fuel:volatility:'.$station->id.':'.$fuelType,
                    0.0015,
                    0.0065
                );
                $trendSlope = $rng->float(
                    'fuel:trend:'.$station->id.':'.$fuelType,
                    -0.0120,
                    0.0150
                );
                $phase = $rng->float(
                    'fuel:phase:'.$station->id.':'.$fuelType,
                    0.000,
                    (float) (2 * pi())
                );

                for ($dayIndex = 0; $dayIndex < $days; $dayIndex++) {
                    $recordedAt = $startDate->addDays($dayIndex)->toDateString();
                    $progress = $dayIndex / ($days - 1);
                    $wave = sin(($dayIndex / 5.0) + $phase) * $volatility;
                    $noise = $rng->float(
                        'fuel:noise:'.$station->id.':'.$fuelType.':'.$recordedAt,
                        -0.0030,
                        0.0030
                    );
                    $dailyMultiplier = 1 + ($trendSlope * $progress) + $wave;
                    $resolvedPrice = round(
                        max(
                            0.550,
                            ($basePrice * $cityMultiplier * $stationMultiplier * $dailyMultiplier) + $noise
                        ),
                        3
                    );

                    $rows[] = [
                        'fuel_station_id' => $station->id,
                        'fuel_type' => $fuelType,
                        'recorded_at' => $recordedAt,
                        'price_eur_liter' => $resolvedPrice,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            FuelPrice::query()->upsert(
                $chunk,
                ['fuel_station_id', 'fuel_type', 'recorded_at'],
                ['price_eur_liter']
            );
        }
    }

    private function cityMultiplier(string $citySlug): float
    {
        return match ($citySlug) {
            'prishtine' => 1.008,
            'prizren' => 0.998,
            'peje' => 0.995,
            'gjilan' => 1.002,
            default => 1.000,
        };
    }

    private function stationMultiplier(?string $brandKey, SeededRng $rng, int $stationId): float
    {
        $normalized = strtolower((string) $brandKey);

        $brandBase = match (true) {
            str_contains($normalized, 'shell') => 1.006,
            str_contains($normalized, 'hib') => 1.000,
            str_contains($normalized, 'al petrol') => 0.997,
            str_contains($normalized, 'alpetrol') => 0.997,
            str_contains($normalized, 'ip petrol') => 1.003,
            str_contains($normalized, 'ippetrol') => 1.003,
            str_contains($normalized, 'liriaoil') => 0.996,
            default => 1.000,
        };

        $variance = $rng->float('fuel:station-factor:'.$stationId, -0.006, 0.006);

        return max(0.960, $brandBase + $variance);
    }
}

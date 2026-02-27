<?php

namespace App\Features\Fuel\Services;

use App\Features\Fuel\Repositories\FuelPriceRepository;
use Illuminate\Support\Collection;

class FuelPriceService
{
    /**
     * @var array<string, string>
     */
    private const BRAND_NAME_BY_KEY = [
        'shell' => 'Shell',
        'hib' => 'HIB',
        'alpetrol' => 'Al Petrol',
        'ippetrol' => 'IP Petrol',
        'liriaoil' => 'Liria Oil',
    ];

    public function __construct(
        private readonly FuelPriceRepository $fuelPriceRepository
    ) {
    }

    public function listLatestPrices(?int $cityId, ?string $fuelType, ?string $recordedAt): Collection
    {
        return $this->withBrandNames(
            $this->fuelPriceRepository->latestByFilter($cityId, $fuelType, $recordedAt)
        );
    }

    public function listLatestByCity(?int $cityId, ?string $fuelType = null): Collection
    {
        return $this->withBrandNames(
            $this->fuelPriceRepository->latestByCity($cityId, $fuelType)
        );
    }

    public function listStations(?int $cityId, ?string $fuelType): Collection
    {
        return $this->withBrandNames(
            $this->fuelPriceRepository->rankedStationsByFilter($cityId, $fuelType)
        );
    }

    public function listBrandSummaries(?int $cityId, ?string $fuelType): Collection
    {
        $stationRows = $this->listStations($cityId, $fuelType);

        return $stationRows
            ->groupBy(fn (object $row): string => (string) ($row->brand_key ?? 'local'))
            ->map(function (Collection $rows, string $brandKey): array {
                $bestRow = $rows->sortBy('price_eur_liter')->first();
                $bestPrice = $bestRow !== null ? round((float) $bestRow->price_eur_liter, 3) : null;

                return [
                    'brand_key' => $brandKey,
                    'brand_name' => $bestRow?->brand_name ?? $this->resolveBrandName($brandKey),
                    'best_price' => $bestPrice,
                    'best_station_name' => $bestRow?->station_name,
                    'station_count' => $rows->pluck('fuel_station_id')->unique()->count(),
                    'updated_at' => $rows->max('recorded_at'),
                ];
            })
            ->sortBy('best_price')
            ->values();
    }

    /**
     * @return array{start_date:?string,end_date:?string,items:Collection}
     */
    public function listHistory(?int $cityId, ?string $type, int $days, ?string $brandKey = null): array
    {
        return $this->fuelPriceRepository->historyByFilter($cityId, $type, $days, $brandKey);
    }

    private function withBrandNames(Collection $rows): Collection
    {
        return $rows->map(function (object $row): object {
            $brandKey = (string) ($row->brand_key ?? 'local');
            $row->brand_key = $brandKey;
            $row->brand_name = $this->resolveBrandName($brandKey);

            return $row;
        });
    }

    private function resolveBrandName(string $brandKey): string
    {
        $normalized = trim(strtolower($brandKey));

        if ($normalized === '') {
            return 'Local Fuel';
        }

        return self::BRAND_NAME_BY_KEY[$normalized]
            ?? ucwords(str_replace(['-', '_'], ' ', $normalized));
    }
}

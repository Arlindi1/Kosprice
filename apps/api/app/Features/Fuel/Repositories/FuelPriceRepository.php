<?php

namespace App\Features\Fuel\Repositories;

use App\Features\Fuel\Models\FuelPrice;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class FuelPriceRepository
{
    public function latestByFilter(?int $cityId, ?string $fuelType, ?string $recordedAt): Collection
    {
        $latestSubQuery = FuelPrice::query()
            ->selectRaw('fuel_station_id, fuel_type, MAX(recorded_at) as latest_recorded_at')
            ->groupBy('fuel_station_id', 'fuel_type');

        if ($fuelType !== null) {
            $latestSubQuery->where('fuel_type', $fuelType);
        }

        if ($recordedAt !== null) {
            $latestSubQuery->whereDate('recorded_at', $recordedAt);
        }

        $query = FuelPrice::query()
            ->select([
                'fuel_prices.id',
                'fuel_prices.fuel_station_id',
                'fuel_prices.fuel_type',
                'fuel_prices.price_eur_liter',
                'fuel_prices.recorded_at',
                'fuel_stations.name as station_name',
                'fuel_stations.address as station_address',
                'cities.id as city_id',
                'cities.name as city_name',
                'cities.slug as city_slug',
            ])
            ->joinSub($latestSubQuery, 'latest_prices', function ($join): void {
                $join->on('fuel_prices.fuel_station_id', '=', 'latest_prices.fuel_station_id')
                    ->on('fuel_prices.fuel_type', '=', 'latest_prices.fuel_type')
                    ->on('fuel_prices.recorded_at', '=', 'latest_prices.latest_recorded_at');
            })
            ->join('fuel_stations', 'fuel_stations.id', '=', 'fuel_prices.fuel_station_id')
            ->join('cities', 'cities.id', '=', 'fuel_stations.city_id')
            ->orderBy('cities.name')
            ->orderBy('fuel_stations.name')
            ->orderBy('fuel_prices.fuel_type');

        if ($fuelType !== null) {
            $query->where('fuel_prices.fuel_type', $fuelType);
        }

        if ($cityId !== null) {
            $query->where('cities.id', $cityId);
        }

        return $query->get();
    }

    public function latestByCity(?int $cityId): Collection
    {
        return $this->latestByFilter($cityId, null, null);
    }

    /**
     * @return array{start_date:?string,end_date:?string,items:Collection}
     */
    public function historyByFilter(?int $cityId, ?string $type, int $days): array
    {
        $maxDateQuery = FuelPrice::query()
            ->join('fuel_stations', 'fuel_stations.id', '=', 'fuel_prices.fuel_station_id');

        if ($cityId !== null) {
            $maxDateQuery->where('fuel_stations.city_id', $cityId);
        }

        if ($type !== null) {
            $maxDateQuery->where('fuel_prices.fuel_type', $type);
        }

        $maxDate = $maxDateQuery->max('fuel_prices.recorded_at');

        if ($maxDate === null) {
            return [
                'start_date' => null,
                'end_date' => null,
                'items' => collect(),
            ];
        }

        $endDate = CarbonImmutable::parse($maxDate);
        $startDate = $endDate->subDays($days - 1)->toDateString();

        $query = FuelPrice::query()
            ->selectRaw(
                'fuel_prices.recorded_at, fuel_prices.fuel_type, ROUND(AVG(fuel_prices.price_eur_liter), 3) as avg_price_eur_liter'
            )
            ->join('fuel_stations', 'fuel_stations.id', '=', 'fuel_prices.fuel_station_id')
            ->whereBetween('fuel_prices.recorded_at', [$startDate, $endDate->toDateString()])
            ->groupBy('fuel_prices.recorded_at', 'fuel_prices.fuel_type')
            ->orderBy('fuel_prices.recorded_at')
            ->orderBy('fuel_prices.fuel_type');

        if ($cityId !== null) {
            $query->where('fuel_stations.city_id', $cityId);
        }

        if ($type !== null) {
            $query->where('fuel_prices.fuel_type', $type);
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate->toDateString(),
            'items' => $query->get(),
        ];
    }
}

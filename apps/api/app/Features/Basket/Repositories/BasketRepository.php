<?php

namespace App\Features\Basket\Repositories;

use App\Features\Markets\Models\MarketPrice;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BasketRepository
{
    public function latestTotals(?int $cityId, ?int $marketId): Collection
    {
        $latestRecordedAtSubQuery = MarketPrice::query()
            ->selectRaw('market_id, MAX(recorded_at) as latest_recorded_at')
            ->groupBy('market_id');

        $query = MarketPrice::query()
            ->selectRaw(
                'market_prices.market_id, markets.name as market_name, cities.id as city_id, cities.name as city_name, cities.slug as city_slug, latest_market_dates.latest_recorded_at as recorded_at, ROUND(SUM(market_prices.price_eur), 2) as total_price_eur'
            )
            ->joinSub($latestRecordedAtSubQuery, 'latest_market_dates', function ($join): void {
                $join->on('market_prices.market_id', '=', 'latest_market_dates.market_id')
                    ->on('market_prices.recorded_at', '=', 'latest_market_dates.latest_recorded_at');
            })
            ->join('markets', 'markets.id', '=', 'market_prices.market_id')
            ->join('cities', 'cities.id', '=', 'markets.city_id')
            ->groupBy(
                'market_prices.market_id',
                'markets.name',
                'cities.id',
                'cities.name',
                'cities.slug',
                'latest_market_dates.latest_recorded_at'
            )
            ->orderBy('total_price_eur')
            ->orderBy('markets.name');

        if ($cityId !== null) {
            $query->where('cities.id', $cityId);
        }

        if ($marketId !== null) {
            $query->where('markets.id', $marketId);
        }

        return $query->get();
    }

    /**
     * @return array{start_date:?string,end_date:?string,items:Collection}
     */
    public function trendByCity(int $cityId, int $days): array
    {
        $maxDate = MarketPrice::query()
            ->join('markets', 'markets.id', '=', 'market_prices.market_id')
            ->where('markets.city_id', $cityId)
            ->max('market_prices.recorded_at');

        if ($maxDate === null) {
            return [
                'start_date' => null,
                'end_date' => null,
                'items' => collect(),
            ];
        }

        $endDate = CarbonImmutable::parse($maxDate);
        $startDate = $endDate->subDays($days - 1)->toDateString();

        $dailyMarketTotals = MarketPrice::query()
            ->selectRaw('market_prices.recorded_at, market_prices.market_id, SUM(market_prices.price_eur) as market_total')
            ->join('markets', 'markets.id', '=', 'market_prices.market_id')
            ->where('markets.city_id', $cityId)
            ->whereBetween('market_prices.recorded_at', [$startDate, $endDate->toDateString()])
            ->groupBy('market_prices.recorded_at', 'market_prices.market_id');

        $items = DB::query()
            ->fromSub($dailyMarketTotals, 'daily_market_totals')
            ->selectRaw(
                'recorded_at, ROUND(AVG(market_total), 2) as average_total_eur, ROUND(MIN(market_total), 2) as min_total_eur, ROUND(MAX(market_total), 2) as max_total_eur'
            )
            ->groupBy('recorded_at')
            ->orderBy('recorded_at')
            ->get();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate->toDateString(),
            'items' => $items,
        ];
    }
}

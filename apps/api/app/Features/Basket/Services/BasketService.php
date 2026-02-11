<?php

namespace App\Features\Basket\Services;

use App\Features\Basket\Repositories\BasketRepository;
use App\Features\Markets\Models\Market;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BasketService
{
    public function __construct(
        private readonly BasketRepository $basketRepository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function total(?int $cityId, int $marketId): array
    {
        $row = $this->basketRepository->latestTotals($cityId, $marketId)->first();

        if ($row === null) {
            throw (new ModelNotFoundException())->setModel(Market::class, $marketId);
        }

        return $this->mapSummary($row);
    }

    /**
     * @return array<string, mixed>
     */
    public function cheapest(int $cityId): array
    {
        $row = $this->basketRepository->latestTotals($cityId, null)->first();

        if ($row === null) {
            throw (new ModelNotFoundException())->setModel(Market::class);
        }

        return $this->mapSummary($row);
    }

    /**
     * @return array{start_date:?string,end_date:?string,items:\Illuminate\Support\Collection}
     */
    public function trend(int $cityId, int $days): array
    {
        return $this->basketRepository->trendByCity($cityId, $days);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSummary(object $row): array
    {
        return [
            'market_id' => $row->market_id,
            'market_name' => $row->market_name,
            'city_id' => $row->city_id,
            'city_name' => $row->city_name,
            'city_slug' => $row->city_slug,
            'recorded_at' => $row->recorded_at,
            'total_price_eur' => (float) $row->total_price_eur,
        ];
    }
}

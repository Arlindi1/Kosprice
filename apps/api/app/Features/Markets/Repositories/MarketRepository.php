<?php

namespace App\Features\Markets\Repositories;

use App\Features\Markets\Models\Market;
use App\Features\Markets\Models\MarketPrice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class MarketRepository
{
    public function listWithCity(?int $cityId): Collection
    {
        $query = Market::query()
            ->with('city')
            ->orderBy('name');

        if ($cityId !== null) {
            $query->where('city_id', $cityId);
        }

        return $query->get();
    }

    public function findWithCity(int $marketId): ?Market
    {
        return Market::query()
            ->with('city')
            ->find($marketId);
    }

    /**
     * @return array{recorded_at:?string,items:SupportCollection}
     */
    public function basketItems(int $marketId, ?string $recordedAt): array
    {
        $resolvedDate = $recordedAt;

        if ($resolvedDate === null) {
            $resolvedDate = MarketPrice::query()
                ->where('market_id', $marketId)
                ->max('recorded_at');
        }

        if ($resolvedDate === null) {
            return [
                'recorded_at' => null,
                'items' => collect(),
            ];
        }

        $items = MarketPrice::query()
            ->select([
                'market_prices.product_id',
                'products.name as product_name',
                'products.unit as product_unit',
                'market_prices.price_eur',
            ])
            ->join('products', 'products.id', '=', 'market_prices.product_id')
            ->where('market_prices.market_id', $marketId)
            ->whereDate('market_prices.recorded_at', $resolvedDate)
            ->orderBy('products.name')
            ->get();

        return [
            'recorded_at' => $resolvedDate,
            'items' => $items,
        ];
    }
}

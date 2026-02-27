<?php

namespace App\Features\Products\Repositories;

use App\Features\Markets\Models\MarketPrice;
use App\Features\Markets\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class ProductRepository
{
    public function listByCategory(?string $category): Collection
    {
        $query = Product::query()
            ->orderBy('category')
            ->orderBy('name');

        if ($category !== null) {
            $query->where('category', $category);
        }

        return $query->get();
    }

    /**
     * @return array{items:Collection,recorded_at:?string}
     */
    public function catalogByCity(int $cityId): array
    {
        $latestRecordedAt = MarketPrice::query()
            ->join('markets', 'markets.id', '=', 'market_prices.market_id')
            ->where('markets.city_id', $cityId)
            ->max('market_prices.recorded_at');

        if ($latestRecordedAt === null) {
            $items = Product::query()
                ->select([
                    'products.id',
                    'products.name',
                    'products.brand',
                    'products.variant',
                    'products.category',
                    'products.image_key',
                    'products.unit_label',
                    'products.is_core_basket',
                    DB::raw('NULL as cheapest_price_today'),
                    DB::raw('NULL as cheapest_market_name'),
                ])
                ->orderBy('products.category')
                ->orderBy('products.name')
                ->get();

            return [
                'items' => $items,
                'recorded_at' => null,
            ];
        }

        $cheapestPriceQuery = MarketPrice::query()
            ->selectRaw('market_prices.product_id, MIN(market_prices.price_eur) as cheapest_price_today')
            ->join('markets', 'markets.id', '=', 'market_prices.market_id')
            ->where('markets.city_id', $cityId)
            ->whereDate('market_prices.recorded_at', $latestRecordedAt)
            ->groupBy('market_prices.product_id');

        $cheapestMarketQuery = MarketPrice::query()
            ->selectRaw('market_prices.product_id, MIN(markets.name) as cheapest_market_name')
            ->join('markets', 'markets.id', '=', 'market_prices.market_id')
            ->joinSub($cheapestPriceQuery, 'cheapest_prices', function ($join): void {
                $join->on('market_prices.product_id', '=', 'cheapest_prices.product_id')
                    ->on('market_prices.price_eur', '=', 'cheapest_prices.cheapest_price_today');
            })
            ->where('markets.city_id', $cityId)
            ->whereDate('market_prices.recorded_at', $latestRecordedAt)
            ->groupBy('market_prices.product_id');

        $items = Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.brand',
                'products.variant',
                'products.category',
                'products.image_key',
                'products.unit_label',
                'products.is_core_basket',
                'cheapest_prices.cheapest_price_today',
                'cheapest_markets.cheapest_market_name',
            ])
            ->leftJoinSub($cheapestPriceQuery, 'cheapest_prices', function ($join): void {
                $join->on('products.id', '=', 'cheapest_prices.product_id');
            })
            ->leftJoinSub($cheapestMarketQuery, 'cheapest_markets', function ($join): void {
                $join->on('products.id', '=', 'cheapest_markets.product_id');
            })
            ->orderBy('products.category')
            ->orderBy('products.name')
            ->get();

        return [
            'items' => $items,
            'recorded_at' => $latestRecordedAt,
        ];
    }

    public function latestRecordedAtByCity(int $cityId): ?string
    {
        return MarketPrice::query()
            ->join('markets', 'markets.id', '=', 'market_prices.market_id')
            ->where('markets.city_id', $cityId)
            ->max('market_prices.recorded_at');
    }

    public function cityNameById(int $cityId): ?string
    {
        return DB::table('cities')
            ->where('id', $cityId)
            ->value('name');
    }

    public function citySlugById(int $cityId): ?string
    {
        return DB::table('cities')
            ->where('id', $cityId)
            ->value('slug');
    }

    /**
     * @return SupportCollection<int, object>
     */
    public function cheapestMarketsForProduct(
        int $productId,
        int $cityId,
        string $recordedAt,
        int $limit = 5
    ): SupportCollection {
        return MarketPrice::query()
            ->select([
                'markets.id as market_id',
                'markets.name as market_name',
                'markets.address as market_address',
                'market_prices.price_eur',
            ])
            ->join('markets', 'markets.id', '=', 'market_prices.market_id')
            ->where('market_prices.product_id', $productId)
            ->where('markets.city_id', $cityId)
            ->whereDate('market_prices.recorded_at', $recordedAt)
            ->orderBy('market_prices.price_eur')
            ->orderBy('markets.name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return SupportCollection<int, object>
     */
    public function pricesForProductByCity(
        int $productId,
        int $cityId,
    ): SupportCollection {
        $latestByMarketQuery = MarketPrice::query()
            ->selectRaw('market_prices.market_id, MAX(market_prices.recorded_at) as latest_recorded_at')
            ->join('markets', 'markets.id', '=', 'market_prices.market_id')
            ->where('market_prices.product_id', $productId)
            ->where('markets.city_id', $cityId)
            ->groupBy('market_prices.market_id');

        return MarketPrice::query()
            ->select([
                'markets.id as market_id',
                'markets.name as market_name',
                'markets.address as market_address',
                'market_prices.price_eur',
                'market_prices.recorded_at',
            ])
            ->join('markets', 'markets.id', '=', 'market_prices.market_id')
            ->joinSub($latestByMarketQuery, 'latest_by_market', function ($join): void {
                $join->on('market_prices.market_id', '=', 'latest_by_market.market_id')
                    ->on('market_prices.recorded_at', '=', 'latest_by_market.latest_recorded_at');
            })
            ->where('market_prices.product_id', $productId)
            ->where('markets.city_id', $cityId)
            ->orderBy('market_prices.price_eur')
            ->orderBy('markets.name')
            ->get();
    }
}

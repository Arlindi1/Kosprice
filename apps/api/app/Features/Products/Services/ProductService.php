<?php

namespace App\Features\Products\Services;

use App\Features\Cities\Models\City;
use App\Features\Markets\Models\Product;
use App\Features\Products\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * @return array{items:\Illuminate\Database\Eloquent\Collection,meta:array<string, mixed>}
     */
    public function listProducts(?string $category): array
    {
        $items = $this->productRepository->listByCategory($category);

        return [
            'items' => $items,
            'meta' => [
                'count' => $items->count(),
                'category' => $category,
            ],
        ];
    }

    /**
     * @return array{items:\Illuminate\Database\Eloquent\Collection,meta:array<string, mixed>}
     */
    public function listCatalog(int $cityId): array
    {
        $catalog = $this->productRepository->catalogByCity($cityId);

        return [
            'items' => $catalog['items'],
            'meta' => [
                'count' => $catalog['items']->count(),
                'city_id' => $cityId,
                'recorded_at' => $catalog['recorded_at'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cheapestByCity(Product $product, int $cityId): array
    {
        $cityName = $this->productRepository->cityNameById($cityId);
        $citySlug = $this->productRepository->citySlugById($cityId);

        if ($cityName === null || $citySlug === null) {
            throw (new ModelNotFoundException())->setModel(City::class, $cityId);
        }

        $recordedAt = $this->productRepository->latestRecordedAtByCity($cityId);

        if ($recordedAt === null) {
            return [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'variant' => $product->variant,
                    'category' => $product->category,
                    'image_key' => $product->image_key,
                    'unit_label' => $product->unit_label,
                    'is_core_basket' => (bool) $product->is_core_basket,
                ],
                'city' => [
                    'id' => $cityId,
                    'name' => $cityName,
                    'slug' => $citySlug,
                ],
                'recorded_at' => null,
                'cheapest' => null,
                'alternatives' => [],
            ];
        }

        $rows = $this->productRepository->cheapestMarketsForProduct(
            $product->id,
            $cityId,
            $recordedAt,
            5
        );
        $cheapestRow = $rows->first();
        $cheapestPrice = $cheapestRow !== null ? (float) $cheapestRow->price_eur : null;

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'variant' => $product->variant,
                'category' => $product->category,
                'image_key' => $product->image_key,
                'unit_label' => $product->unit_label,
                'is_core_basket' => (bool) $product->is_core_basket,
            ],
            'city' => [
                'id' => $cityId,
                'name' => $cityName,
                'slug' => $citySlug,
            ],
            'recorded_at' => $recordedAt,
            'cheapest' => $cheapestRow !== null && $cheapestPrice !== null
                ? $this->mapMarketPrice($cheapestRow, $cheapestPrice)
                : null,
            'alternatives' => $this->mapAlternatives($rows, $cheapestPrice),
        ];
    }

    /**
     * @return array{data:array<string, mixed>,meta:array<string, mixed>}
     */
    public function pricesByCity(Product $product, int $cityId): array
    {
        $cityName = $this->productRepository->cityNameById($cityId);
        $citySlug = $this->productRepository->citySlugById($cityId);

        if ($cityName === null || $citySlug === null) {
            throw (new ModelNotFoundException())->setModel(City::class, $cityId);
        }

        $rows = $this->productRepository->pricesForProductByCity($product->id, $cityId);
        $prices = $rows
            ->map(function (object $row): array {
                return [
                    'market_id' => (int) $row->market_id,
                    'market_name' => (string) $row->market_name,
                    'address' => $row->market_address,
                    'price_eur' => (float) $row->price_eur,
                    'recorded_at' => $row->recorded_at,
                ];
            })
            ->values()
            ->all();

        $updatedAt = $rows->max('recorded_at');

        return [
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'variant' => $product->variant,
                    'unit_label' => $product->unit_label,
                    'category' => $product->category,
                    'image_key' => $product->image_key,
                ],
                'city' => [
                    'id' => $cityId,
                    'name' => $cityName,
                    'slug' => $citySlug,
                ],
                'prices' => $prices,
            ],
            'meta' => [
                'count' => count($prices),
                'updated_at' => $updatedAt,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapMarketPrice(object $row, float $cheapestPrice): array
    {
        $price = (float) $row->price_eur;

        return [
            'market' => [
                'id' => (int) $row->market_id,
                'name' => (string) $row->market_name,
                'address' => $row->market_address,
            ],
            'price_eur' => $price,
            'delta_from_cheapest_eur' => round($price - $cheapestPrice, 2),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapAlternatives(Collection $rows, ?float $cheapestPrice): array
    {
        if ($cheapestPrice === null) {
            return [];
        }

        return $rows
            ->slice(1)
            ->take(4)
            ->map(fn (object $row): array => $this->mapMarketPrice($row, $cheapestPrice))
            ->values()
            ->all();
    }
}

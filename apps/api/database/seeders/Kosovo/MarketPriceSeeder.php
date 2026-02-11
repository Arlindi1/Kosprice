<?php

namespace Database\Seeders\Kosovo;

use App\Features\Markets\Models\Market;
use App\Features\Markets\Models\MarketPrice;
use App\Features\Markets\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class MarketPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $marketIds = Market::query()->pluck('id', 'name');
        $productIds = Product::query()->pluck('id', 'name');
        $startDate = CarbonImmutable::parse('2026-01-12');
        $days = 30;

        $basePrices = [
            'ETC Prishtine' => [
                'Bread 500g' => 0.60,
                'Milk 1L' => 1.20,
                'Eggs 10pcs' => 2.30,
                'Rice 1kg' => 1.45,
                'Chicken 1kg' => 5.10,
                'Sunflower Oil 1L' => 1.75,
            ],
            'Viva Fresh Prishtine' => [
                'Bread 500g' => 0.58,
                'Milk 1L' => 1.18,
                'Eggs 10pcs' => 2.25,
                'Rice 1kg' => 1.49,
                'Chicken 1kg' => 5.20,
                'Sunflower Oil 1L' => 1.80,
            ],
            'Interex Prizren' => [
                'Bread 500g' => 0.59,
                'Milk 1L' => 1.19,
                'Eggs 10pcs' => 2.20,
                'Rice 1kg' => 1.42,
                'Chicken 1kg' => 5.05,
                'Sunflower Oil 1L' => 1.73,
            ],
            'Elkos Market Peje' => [
                'Bread 500g' => 0.57,
                'Milk 1L' => 1.17,
                'Eggs 10pcs' => 2.18,
                'Rice 1kg' => 1.40,
                'Chicken 1kg' => 4.98,
                'Sunflower Oil 1L' => 1.70,
            ],
            'Meridian Express Gjilan' => [
                'Bread 500g' => 0.61,
                'Milk 1L' => 1.22,
                'Eggs 10pcs' => 2.32,
                'Rice 1kg' => 1.48,
                'Chicken 1kg' => 5.18,
                'Sunflower Oil 1L' => 1.79,
            ],
        ];

        foreach ($basePrices as $marketName => $prices) {
            foreach ($prices as $productName => $price) {
                $marketId = $marketIds[$marketName];
                $productId = $productIds[$productName];

                for ($dayIndex = 0; $dayIndex < $days; $dayIndex++) {
                    $recordedAt = $startDate->addDays($dayIndex)->toDateString();
                    $isLatestDay = $dayIndex === ($days - 1);
                    $progress = $dayIndex / ($days - 1);
                    $dailySwing = (($dayIndex % 7) - 3) * 0.005;

                    $resolvedPrice = $isLatestDay
                        ? round($price + 0.03, 2)
                        : round($price + (0.03 * $progress) + $dailySwing, 2);

                    MarketPrice::query()->updateOrCreate(
                        [
                            'market_id' => $marketId,
                            'product_id' => $productId,
                            'recorded_at' => $recordedAt,
                        ],
                        ['price_eur' => $resolvedPrice]
                    );
                }
            }
        }
    }
}

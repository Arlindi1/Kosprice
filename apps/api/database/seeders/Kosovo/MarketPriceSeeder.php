<?php

namespace Database\Seeders\Kosovo;

use App\Features\Markets\Models\Market;
use App\Features\Markets\Models\MarketPrice;
use App\Features\Markets\Models\Product;
use Carbon\CarbonImmutable;
use Database\Seeders\Kosovo\Support\SeededRng;
use Illuminate\Database\Seeder;

class MarketPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $markets = Market::query()
            ->with('city:id,slug')
            ->get(['id', 'city_id', 'name']);
        $products = Product::query()
            ->where('is_core_basket', true)
            ->orderBy('id')
            ->get(['id', 'name']);

        if ($markets->isEmpty() || $products->isEmpty()) {
            return;
        }

        $rng = new SeededRng(20260211);
        $days = 30;
        $endDate = CarbonImmutable::today()->subDay();
        $startDate = $endDate->subDays($days - 1);
        $basePrices = $this->baseProductPrices();
        $rows = [];

        foreach ($markets as $market) {
            $citySlug = $market->city?->slug;

            if ($citySlug === null) {
                continue;
            }

            $cityMultiplier = $this->cityMultiplier($citySlug);
            $marketMultiplier = $this->marketMultiplier($market->name, $rng, $market->id);

            foreach ($products as $product) {
                $basePrice = $basePrices[$product->name]
                    ?? $rng->float('market:fallback-price:'.$product->id, 0.60, 3.80);

                $volatility = $rng->float(
                    'market:volatility:'.$market->id.':'.$product->id,
                    0.004,
                    0.018
                );
                $trendSlope = $rng->float(
                    'market:trend:'.$market->id.':'.$product->id,
                    -0.030,
                    0.040
                );
                $phase = $rng->float(
                    'market:phase:'.$market->id.':'.$product->id,
                    0.000,
                    (float) (2 * pi())
                );

                for ($dayIndex = 0; $dayIndex < $days; $dayIndex++) {
                    $recordedAt = $startDate->addDays($dayIndex)->toDateString();
                    $progress = $dayIndex / ($days - 1);
                    $wave = sin(($dayIndex / 4.5) + $phase) * $volatility;
                    $noise = $rng->float(
                        'market:noise:'.$market->id.':'.$product->id.':'.$recordedAt,
                        -0.010,
                        0.010
                    );
                    $dailyMultiplier = 1 + ($trendSlope * $progress) + $wave + $noise;
                    $resolvedPrice = round(
                        max(
                            0.20,
                            $basePrice * $cityMultiplier * $marketMultiplier * $dailyMultiplier
                        ),
                        2
                    );

                    $rows[] = [
                        'market_id' => $market->id,
                        'product_id' => $product->id,
                        'recorded_at' => $recordedAt,
                        'price_eur' => $resolvedPrice,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            MarketPrice::query()->upsert(
                $chunk,
                ['market_id', 'product_id', 'recorded_at'],
                ['price_eur']
            );
        }
    }

    /**
     * @return array<string, float>
     */
    private function baseProductPrices(): array
    {
        return [
            'Buke Integrale' => 0.72,
            'Qumesht Vita' => 1.19,
            'Qumesht Kosmilk' => 1.14,
            'Veze Fresh' => 2.45,
            'Vaj luledielli Vital' => 1.88,
            'Sheqer Kristal' => 1.24,
            'Miell Mlinpek' => 0.79,
            'Oriz Basmati Gala' => 1.98,
            'Pule e fresket Dukagjini' => 5.75,
            'Patate Vendore' => 0.93,
            'Kafe Grand' => 3.15,
            'Kos Vita' => 1.22,
            'Djathe Sharri i bardhe' => 2.78,
            'Makarona Divella' => 1.09,
            'Fasule Tetove' => 1.96,
            'Qepe e Verdhe' => 0.84,
            'Domate Vendore' => 1.44,
            'Molle Idared' => 1.31,
            'Banane Chiquita' => 1.69,
            'Kripe Solana' => 0.49,
            'Gjalpe President' => 1.86,
            'Uje Rugova' => 0.52,
            'Uje Mokna' => 0.47,
            'Coca-Cola' => 0.69,
            'Pepsi' => 0.67,
            'Fanta Orange' => 0.66,
            'Leng Portokalli Fructal' => 1.29,
            'Caj i zi Podravka' => 1.52,
        ];
    }

    private function cityMultiplier(string $citySlug): float
    {
        return match ($citySlug) {
            'prishtine' => 1.030,
            'prizren' => 1.000,
            'peje' => 0.985,
            'gjilan' => 0.995,
            default => 1.000,
        };
    }

    private function marketMultiplier(string $marketName, SeededRng $rng, int $marketId): float
    {
        $normalized = strtolower($marketName);

        $chainBase = match (true) {
            str_contains($normalized, 'maxi') => 0.992,
            str_contains($normalized, 'meridian') => 1.015,
            str_contains($normalized, 'viva') => 1.010,
            str_contains($normalized, 'interex') => 0.995,
            str_contains($normalized, 'etc') => 1.000,
            default => 1.008,
        };

        $variance = $rng->float('market:factor:'.$marketId, -0.020, 0.020);

        return max(0.900, $chainBase + $variance);
    }
}

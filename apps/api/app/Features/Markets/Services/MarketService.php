<?php

namespace App\Features\Markets\Services;

use App\Features\Markets\Models\Market;
use App\Features\Markets\Repositories\MarketRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MarketService
{
    public function __construct(
        private readonly MarketRepository $marketRepository
    ) {
    }

    public function listMarkets(?int $cityId): Collection
    {
        return $this->marketRepository->listWithCity($cityId);
    }

    /**
     * @return array{market:Market,recorded_at:?string,total_price_eur:float,items:\Illuminate\Support\Collection}
     */
    public function getBasket(int $marketId, ?string $recordedAt): array
    {
        $market = $this->marketRepository->findWithCity($marketId);

        if ($market === null) {
            throw (new ModelNotFoundException())->setModel(Market::class, $marketId);
        }

        $basketData = $this->marketRepository->basketItems($marketId, $recordedAt);
        $totalPrice = $basketData['items']->sum(
            static fn (object $item): float => (float) $item->price_eur
        );

        return [
            'market' => $market,
            'recorded_at' => $basketData['recorded_at'],
            'total_price_eur' => round($totalPrice, 2),
            'items' => $basketData['items'],
        ];
    }
}

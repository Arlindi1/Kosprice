<?php

namespace App\Features\Markets\Http\Controllers;

use App\Features\Markets\Http\Requests\ShowMarketBasketRequest;
use App\Features\Markets\Http\Resources\MarketBasketResource;
use App\Features\Markets\Models\Market;
use App\Features\Markets\Services\MarketService;

class MarketBasketController
{
    public function __construct(
        private readonly MarketService $marketService
    ) {
    }

    public function show(ShowMarketBasketRequest $request, Market $market): MarketBasketResource
    {
        $basket = $this->marketService->getBasket($market->id, $request->recordedAt());

        return new MarketBasketResource($basket);
    }
}

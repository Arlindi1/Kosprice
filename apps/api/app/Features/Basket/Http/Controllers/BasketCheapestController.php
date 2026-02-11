<?php

namespace App\Features\Basket\Http\Controllers;

use App\Features\Basket\Http\Requests\ShowCheapestBasketRequest;
use App\Features\Basket\Http\Resources\BasketSummaryResource;
use App\Features\Basket\Services\BasketService;

class BasketCheapestController
{
    public function __construct(
        private readonly BasketService $basketService
    ) {
    }

    public function show(ShowCheapestBasketRequest $request): BasketSummaryResource
    {
        return new BasketSummaryResource(
            $this->basketService->cheapest($request->cityId())
        );
    }
}

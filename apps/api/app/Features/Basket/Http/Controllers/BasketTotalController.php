<?php

namespace App\Features\Basket\Http\Controllers;

use App\Features\Basket\Http\Requests\ShowBasketTotalRequest;
use App\Features\Basket\Http\Resources\BasketSummaryResource;
use App\Features\Basket\Services\BasketService;

class BasketTotalController
{
    public function __construct(
        private readonly BasketService $basketService
    ) {
    }

    public function show(ShowBasketTotalRequest $request): BasketSummaryResource
    {
        return new BasketSummaryResource(
            $this->basketService->total($request->cityId(), $request->marketId())
        );
    }
}

<?php

namespace App\Features\Basket\Http\Controllers;

use App\Features\Basket\Http\Requests\ListBasketTrendRequest;
use App\Features\Basket\Http\Resources\BasketTrendItemResource;
use App\Features\Basket\Services\BasketService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BasketTrendController
{
    public function __construct(
        private readonly BasketService $basketService
    ) {
    }

    public function index(ListBasketTrendRequest $request): AnonymousResourceCollection
    {
        $trend = $this->basketService->trend($request->cityId(), $request->days());

        return BasketTrendItemResource::collection($trend['items'])
            ->additional([
                'meta' => [
                    'city_id' => $request->cityId(),
                    'days' => $request->days(),
                    'start_date' => $trend['start_date'],
                    'end_date' => $trend['end_date'],
                    'count' => $trend['items']->count(),
                ],
            ]);
    }
}

<?php

namespace App\Features\Markets\Http\Controllers;

use App\Features\Markets\Http\Requests\ListMarketsRequest;
use App\Features\Markets\Http\Resources\MarketResource;
use App\Features\Markets\Services\MarketService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketController
{
    public function __construct(
        private readonly MarketService $marketService
    ) {
    }

    public function index(ListMarketsRequest $request): AnonymousResourceCollection
    {
        return MarketResource::collection(
            $this->marketService->listMarkets($request->cityId())
        );
    }
}

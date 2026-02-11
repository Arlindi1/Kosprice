<?php

namespace App\Features\Fuel\Http\Controllers;

use App\Features\Fuel\Http\Requests\ListFuelPricesRequest;
use App\Features\Fuel\Http\Resources\FuelPriceResource;
use App\Features\Fuel\Services\FuelPriceService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FuelPriceController
{
    public function __construct(
        private readonly FuelPriceService $fuelPriceService
    ) {
    }

    public function index(ListFuelPricesRequest $request): AnonymousResourceCollection
    {
        return FuelPriceResource::collection(
            $this->fuelPriceService->listLatestPrices(
                $request->cityId(),
                $request->fuelType(),
                $request->recordedAt()
            )
        );
    }
}

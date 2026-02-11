<?php

namespace App\Features\Fuel\Http\Controllers;

use App\Features\Fuel\Http\Requests\ListFuelLatestRequest;
use App\Features\Fuel\Http\Resources\FuelPriceResource;
use App\Features\Fuel\Services\FuelPriceService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FuelLatestController
{
    public function __construct(
        private readonly FuelPriceService $fuelPriceService
    ) {
    }

    public function index(ListFuelLatestRequest $request): AnonymousResourceCollection
    {
        return FuelPriceResource::collection(
            $this->fuelPriceService->listLatestByCity($request->cityId())
        );
    }
}

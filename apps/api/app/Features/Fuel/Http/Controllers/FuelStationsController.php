<?php

namespace App\Features\Fuel\Http\Controllers;

use App\Features\Fuel\Http\Requests\ListFuelStationsRequest;
use App\Features\Fuel\Http\Resources\FuelStationRankResource;
use App\Features\Fuel\Services\FuelPriceService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FuelStationsController
{
    public function __construct(
        private readonly FuelPriceService $fuelPriceService
    ) {
    }

    public function index(ListFuelStationsRequest $request): AnonymousResourceCollection
    {
        $items = $this->fuelPriceService->listStations(
            $request->cityId(),
            $request->fuelType()
        );

        return FuelStationRankResource::collection($items)
            ->additional([
                'meta' => [
                    'city_id' => $request->cityId(),
                    'type' => $request->fuelType(),
                    'count' => $items->count(),
                    'updated_at' => $items->max('recorded_at'),
                ],
            ]);
    }
}


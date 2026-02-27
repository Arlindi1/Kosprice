<?php

namespace App\Features\Fuel\Http\Controllers;

use App\Features\Fuel\Http\Requests\ListFuelBrandsRequest;
use App\Features\Fuel\Http\Resources\FuelBrandSummaryResource;
use App\Features\Fuel\Services\FuelPriceService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FuelBrandsController
{
    public function __construct(
        private readonly FuelPriceService $fuelPriceService
    ) {
    }

    public function index(ListFuelBrandsRequest $request): AnonymousResourceCollection
    {
        $items = $this->fuelPriceService->listBrandSummaries(
            $request->cityId(),
            $request->fuelType()
        );

        return FuelBrandSummaryResource::collection($items)
            ->additional([
                'meta' => [
                    'city_id' => $request->cityId(),
                    'type' => $request->fuelType(),
                    'count' => $items->count(),
                ],
            ]);
    }
}


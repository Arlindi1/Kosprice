<?php

namespace App\Features\Fuel\Http\Controllers;

use App\Features\Fuel\Http\Requests\ListFuelHistoryRequest;
use App\Features\Fuel\Http\Resources\FuelHistoryItemResource;
use App\Features\Fuel\Services\FuelPriceService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FuelHistoryController
{
    public function __construct(
        private readonly FuelPriceService $fuelPriceService
    ) {
    }

    public function index(ListFuelHistoryRequest $request): AnonymousResourceCollection
    {
        $history = $this->fuelPriceService->listHistory(
            $request->cityId(),
            $request->type(),
            $request->days(),
            $request->brandKey()
        );

        return FuelHistoryItemResource::collection($history['items'])
            ->additional([
                'meta' => [
                    'city_id' => $request->cityId(),
                    'type' => $request->type(),
                    'brand_key' => $request->brandKey(),
                    'days' => $request->days(),
                    'start_date' => $history['start_date'],
                    'end_date' => $history['end_date'],
                    'count' => $history['items']->count(),
                ],
            ]);
    }
}

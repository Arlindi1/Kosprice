<?php

namespace App\Features\Cities\Http\Controllers;

use App\Features\Cities\Http\Resources\CityResource;
use App\Features\Cities\Services\CityService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CityController
{
    public function __construct(
        private readonly CityService $cityService
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        return CityResource::collection($this->cityService->listCities());
    }
}

<?php

namespace App\Features\Cities\Services;

use App\Features\Cities\Repositories\CityRepository;
use Illuminate\Database\Eloquent\Collection;

class CityService
{
    public function __construct(
        private readonly CityRepository $cityRepository
    ) {
    }

    public function listCities(): Collection
    {
        return $this->cityRepository->allOrdered();
    }
}

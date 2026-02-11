<?php

namespace App\Features\Fuel\Services;

use App\Features\Fuel\Repositories\FuelPriceRepository;
use Illuminate\Support\Collection;

class FuelPriceService
{
    public function __construct(
        private readonly FuelPriceRepository $fuelPriceRepository
    ) {
    }

    public function listLatestPrices(?int $cityId, ?string $fuelType, ?string $recordedAt): Collection
    {
        return $this->fuelPriceRepository->latestByFilter($cityId, $fuelType, $recordedAt);
    }
}

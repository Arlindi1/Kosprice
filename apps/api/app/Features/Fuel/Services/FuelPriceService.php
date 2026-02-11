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

    public function listLatestByCity(?int $cityId): Collection
    {
        return $this->fuelPriceRepository->latestByCity($cityId);
    }

    /**
     * @return array{start_date:?string,end_date:?string,items:Collection}
     */
    public function listHistory(?int $cityId, ?string $type, int $days): array
    {
        return $this->fuelPriceRepository->historyByFilter($cityId, $type, $days);
    }
}

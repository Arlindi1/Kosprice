<?php

namespace App\Features\Fuel\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuelStationRankResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'station_id' => $this->fuel_station_id,
            'brand_key' => $this->brand_key,
            'brand_name' => $this->brand_name,
            'station_name' => $this->station_name,
            'address' => $this->station_address,
            'fuel_type' => $this->fuel_type,
            'city_id' => $this->city_id,
            'city_name' => $this->city_name,
            'city_slug' => $this->city_slug,
            'price_eur_per_l' => round((float) $this->price_eur_liter, 3),
            'recorded_at' => is_string($this->recorded_at)
                ? $this->recorded_at
                : $this->recorded_at?->format('Y-m-d'),
        ];
    }
}


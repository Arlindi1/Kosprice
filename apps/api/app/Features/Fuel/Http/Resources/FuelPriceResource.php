<?php

namespace App\Features\Fuel\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuelPriceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fuel_type' => $this->fuel_type,
            'price_eur_liter' => round((float) $this->price_eur_liter, 3),
            'recorded_at' => is_string($this->recorded_at)
                ? $this->recorded_at
                : $this->recorded_at?->format('Y-m-d'),
            'station' => [
                'id' => $this->fuel_station_id,
                'name' => $this->station_name,
                'address' => $this->station_address,
            ],
            'city' => [
                'id' => $this->city_id,
                'name' => $this->city_name,
                'slug' => $this->city_slug,
            ],
        ];
    }
}

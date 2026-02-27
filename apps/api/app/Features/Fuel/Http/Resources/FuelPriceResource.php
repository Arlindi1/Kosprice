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
            'brand_key' => $this->brand_key,
            'brand_name' => $this->brand_name,
            'station_name' => $this->station_name,
            'address' => $this->station_address,
            'city_id' => $this->city_id,
            'fuel_type' => $this->fuel_type,
            'price_eur_per_l' => round((float) $this->price_eur_liter, 3),
            'price_eur_liter' => round((float) $this->price_eur_liter, 3),
            'recorded_at' => is_string($this->recorded_at)
                ? $this->recorded_at
                : $this->recorded_at?->format('Y-m-d'),
            'station' => [
                'id' => $this->fuel_station_id,
                'name' => $this->station_name,
                'address' => $this->station_address,
                'brand_key' => $this->brand_key,
                'brand_name' => $this->brand_name,
            ],
            'city' => [
                'id' => $this->city_id,
                'name' => $this->city_name,
                'slug' => $this->city_slug,
            ],
        ];
    }
}

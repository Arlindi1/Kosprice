<?php

namespace App\Features\Fuel\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuelBrandSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'brand_key' => $this['brand_key'],
            'brand_name' => $this['brand_name'],
            'best_price' => $this['best_price'],
            'best_station_name' => $this['best_station_name'],
            'station_count' => $this['station_count'],
            'updated_at' => $this['updated_at'],
        ];
    }
}


<?php

namespace App\Features\Fuel\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuelHistoryItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'recorded_at' => is_string($this->recorded_at)
                ? $this->recorded_at
                : $this->recorded_at?->format('Y-m-d'),
            'fuel_type' => $this->fuel_type,
            'avg_price_eur_liter' => round((float) $this->avg_price_eur_liter, 3),
        ];
    }
}

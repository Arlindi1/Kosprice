<?php

namespace App\Features\Basket\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketTrendItemResource extends JsonResource
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
            'average_total_eur' => round((float) $this->average_total_eur, 2),
            'min_total_eur' => round((float) $this->min_total_eur, 2),
            'max_total_eur' => round((float) $this->max_total_eur, 2),
        ];
    }
}

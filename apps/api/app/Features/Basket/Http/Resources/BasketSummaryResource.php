<?php

namespace App\Features\Basket\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BasketSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'market' => [
                'id' => $this['market_id'],
                'name' => $this['market_name'],
            ],
            'city' => [
                'id' => $this['city_id'],
                'name' => $this['city_name'],
                'slug' => $this['city_slug'],
            ],
            'recorded_at' => is_string($this['recorded_at'])
                ? $this['recorded_at']
                : $this['recorded_at']?->format('Y-m-d'),
            'total_price_eur' => round((float) $this['total_price_eur'], 2),
        ];
    }
}

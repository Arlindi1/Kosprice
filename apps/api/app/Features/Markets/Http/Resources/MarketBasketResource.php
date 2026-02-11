<?php

namespace App\Features\Markets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketBasketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'market' => new MarketResource($this['market']),
            'recorded_at' => $this['recorded_at'],
            'total_price_eur' => round((float) $this['total_price_eur'], 2),
            'items' => MarketBasketItemResource::collection($this['items']),
        ];
    }
}

<?php

namespace App\Features\Markets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketBasketItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'name' => $this->product_name,
            'unit' => $this->product_unit,
            'price_eur' => round((float) $this->price_eur, 2),
        ];
    }
}

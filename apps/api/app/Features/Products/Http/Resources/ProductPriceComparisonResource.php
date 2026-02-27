<?php

namespace App\Features\Products\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPriceComparisonResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product' => $this['product'],
            'city' => $this['city'],
            'prices' => $this['prices'],
        ];
    }
}

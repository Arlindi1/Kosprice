<?php

namespace App\Features\Products\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCheapestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product' => $this['product'],
            'city' => $this['city'],
            'recorded_at' => $this['recorded_at'],
            'cheapest' => $this['cheapest'],
            'alternatives' => $this['alternatives'],
        ];
    }
}

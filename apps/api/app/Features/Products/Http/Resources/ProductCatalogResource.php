<?php

namespace App\Features\Products\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCatalogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'variant' => $this->variant,
            'category' => $this->category,
            'image_key' => $this->image_key,
            'unit_label' => $this->unit_label,
            'is_core_basket' => (bool) $this->is_core_basket,
            'cheapest_price_today' => $this->cheapest_price_today !== null
                ? round((float) $this->cheapest_price_today, 2)
                : null,
            'cheapest_market_name' => $this->cheapest_market_name,
        ];
    }
}

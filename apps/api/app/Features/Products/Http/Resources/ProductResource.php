<?php

namespace App\Features\Products\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'unit' => $this->unit,
            'unit_label' => $this->unit_label,
            'brand_hint' => $this->brand_hint,
            'is_core_basket' => (bool) $this->is_core_basket,
        ];
    }
}

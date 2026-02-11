<?php

namespace App\Features\Markets\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => [
                'id' => $this->city?->id,
                'name' => $this->city?->name,
                'slug' => $this->city?->slug,
            ],
        ];
    }
}

<?php

namespace App\Features\Markets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'variant',
        'category',
        'image_key',
        'unit',
        'unit_label',
        'brand_hint',
        'is_core_basket',
    ];

    protected $casts = [
        'is_core_basket' => 'boolean',
    ];

    public function marketPrices(): HasMany
    {
        return $this->hasMany(MarketPrice::class);
    }
}

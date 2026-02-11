<?php

namespace App\Features\Markets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'product_id',
        'price_eur',
        'recorded_at',
    ];

    protected $casts = [
        'price_eur' => 'decimal:2',
        'recorded_at' => 'date:Y-m-d',
    ];

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

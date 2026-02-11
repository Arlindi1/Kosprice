<?php

namespace App\Features\Fuel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_station_id',
        'fuel_type',
        'price_eur_liter',
        'recorded_at',
    ];

    protected $casts = [
        'price_eur_liter' => 'decimal:3',
        'recorded_at' => 'date:Y-m-d',
    ];

    public function fuelStation(): BelongsTo
    {
        return $this->belongsTo(FuelStation::class);
    }
}

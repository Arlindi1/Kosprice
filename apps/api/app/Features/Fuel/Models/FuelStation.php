<?php

namespace App\Features\Fuel\Models;

use App\Features\Cities\Models\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuelStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'address',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(FuelPrice::class);
    }
}

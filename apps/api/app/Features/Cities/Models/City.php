<?php

namespace App\Features\Cities\Models;

use App\Features\Fuel\Models\FuelStation;
use App\Features\Markets\Models\Market;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function markets(): HasMany
    {
        return $this->hasMany(Market::class);
    }

    public function fuelStations(): HasMany
    {
        return $this->hasMany(FuelStation::class);
    }
}

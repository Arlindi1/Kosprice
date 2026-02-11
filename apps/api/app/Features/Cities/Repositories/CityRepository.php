<?php

namespace App\Features\Cities\Repositories;

use App\Features\Cities\Models\City;
use Illuminate\Database\Eloquent\Collection;

class CityRepository
{
    public function allOrdered(): Collection
    {
        return City::query()
            ->orderBy('name')
            ->get();
    }
}

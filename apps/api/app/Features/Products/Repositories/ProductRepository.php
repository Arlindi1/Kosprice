<?php

namespace App\Features\Products\Repositories;

use App\Features\Markets\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function listByCategory(?string $category): Collection
    {
        $query = Product::query()
            ->orderBy('category')
            ->orderBy('name');

        if ($category !== null) {
            $query->where('category', $category);
        }

        return $query->get();
    }
}

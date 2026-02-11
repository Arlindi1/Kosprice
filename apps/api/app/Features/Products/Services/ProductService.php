<?php

namespace App\Features\Products\Services;

use App\Features\Products\Repositories\ProductRepository;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * @return array{items:\Illuminate\Database\Eloquent\Collection,meta:array<string, mixed>}
     */
    public function listProducts(?string $category): array
    {
        $items = $this->productRepository->listByCategory($category);

        return [
            'items' => $items,
            'meta' => [
                'count' => $items->count(),
                'category' => $category,
            ],
        ];
    }
}

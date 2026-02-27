<?php

namespace App\Features\Products\Http\Controllers;

use App\Features\Markets\Models\Product;
use App\Features\Products\Http\Requests\ShowProductCheapestRequest;
use App\Features\Products\Http\Resources\ProductCheapestResource;
use App\Features\Products\Services\ProductService;

class ProductCheapestController
{
    public function __construct(
        private readonly ProductService $productService
    ) {
    }

    public function show(Product $product, ShowProductCheapestRequest $request): ProductCheapestResource
    {
        return new ProductCheapestResource(
            $this->productService->cheapestByCity($product, $request->cityId())
        );
    }
}

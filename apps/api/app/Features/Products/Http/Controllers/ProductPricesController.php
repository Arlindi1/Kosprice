<?php

namespace App\Features\Products\Http\Controllers;

use App\Features\Markets\Models\Product;
use App\Features\Products\Http\Requests\ShowProductPricesRequest;
use App\Features\Products\Http\Resources\ProductPriceComparisonResource;
use App\Features\Products\Services\ProductService;

class ProductPricesController
{
    public function __construct(
        private readonly ProductService $productService
    ) {
    }

    public function index(Product $product, ShowProductPricesRequest $request): ProductPriceComparisonResource
    {
        $result = $this->productService->pricesByCity($product, $request->cityId());

        return (new ProductPriceComparisonResource($result['data']))
            ->additional(['meta' => $result['meta']]);
    }
}

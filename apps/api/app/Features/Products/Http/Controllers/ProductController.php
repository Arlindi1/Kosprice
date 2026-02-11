<?php

namespace App\Features\Products\Http\Controllers;

use App\Features\Products\Http\Requests\ListProductsRequest;
use App\Features\Products\Http\Resources\ProductResource;
use App\Features\Products\Services\ProductService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController
{
    public function __construct(
        private readonly ProductService $productService
    ) {
    }

    public function index(ListProductsRequest $request): AnonymousResourceCollection
    {
        $result = $this->productService->listProducts($request->category());

        return ProductResource::collection($result['items'])
            ->additional(['meta' => $result['meta']]);
    }
}

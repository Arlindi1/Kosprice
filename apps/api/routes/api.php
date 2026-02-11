<?php

use App\Features\Cities\Http\Controllers\CityController;
use App\Features\Fuel\Http\Controllers\FuelPriceController;
use App\Features\Markets\Http\Controllers\MarketBasketController;
use App\Features\Markets\Http\Controllers\MarketController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/markets', [MarketController::class, 'index']);
    Route::get('/markets/{market}/basket', [MarketBasketController::class, 'show']);
    Route::get('products', [\App\Features\Products\Http\Controllers\ProductController::class, 'index']);
    Route::get('/fuel-prices', [FuelPriceController::class, 'index']);
});

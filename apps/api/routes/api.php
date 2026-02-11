<?php

use App\Features\Basket\Http\Controllers\BasketCheapestController;
use App\Features\Basket\Http\Controllers\BasketTotalController;
use App\Features\Basket\Http\Controllers\BasketTrendController;
use App\Features\Cities\Http\Controllers\CityController;
use App\Features\Fuel\Http\Controllers\FuelHistoryController;
use App\Features\Fuel\Http\Controllers\FuelLatestController;
use App\Features\Fuel\Http\Controllers\FuelPriceController;
use App\Features\Markets\Http\Controllers\MarketBasketController;
use App\Features\Markets\Http\Controllers\MarketController;
use App\Features\Products\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/markets', [MarketController::class, 'index']);
    Route::get('/markets/{market}/basket', [MarketBasketController::class, 'show']);
    Route::get('/products', [ProductController::class, 'index']);

    Route::get('/fuel-prices', [FuelPriceController::class, 'index']);
    Route::get('/fuel/latest', [FuelLatestController::class, 'index']);
    Route::get('/fuel/history', [FuelHistoryController::class, 'index']);

    Route::get('/basket/total', [BasketTotalController::class, 'show']);
    Route::get('/basket/cheapest', [BasketCheapestController::class, 'show']);
    Route::get('/basket/trend', [BasketTrendController::class, 'index']);
});

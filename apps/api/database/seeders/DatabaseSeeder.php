<?php

namespace Database\Seeders;

use Database\Seeders\Kosovo\FuelPriceSeeder;
use Database\Seeders\Kosovo\FuelStationSeeder;
use Database\Seeders\Kosovo\KosovoCitySeeder;
use Database\Seeders\Kosovo\MarketPriceSeeder;
use Database\Seeders\Kosovo\MarketSeeder;
use Database\Seeders\Kosovo\ProductSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            KosovoCitySeeder::class,
            MarketSeeder::class,
            ProductSeeder::class,
            MarketPriceSeeder::class,
            FuelStationSeeder::class,
            FuelPriceSeeder::class,
        ]);
    }
}

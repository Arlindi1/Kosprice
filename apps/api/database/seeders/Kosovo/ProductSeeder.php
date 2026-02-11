<?php

namespace Database\Seeders\Kosovo;

use App\Features\Markets\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Bread 500g', 'unit' => 'piece'],
            ['name' => 'Milk 1L', 'unit' => 'liter'],
            ['name' => 'Eggs 10pcs', 'unit' => 'pack'],
            ['name' => 'Rice 1kg', 'unit' => 'kg'],
            ['name' => 'Chicken 1kg', 'unit' => 'kg'],
            ['name' => 'Sunflower Oil 1L', 'unit' => 'liter'],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['name' => $product['name']],
                ['unit' => $product['unit']]
            );
        }
    }
}

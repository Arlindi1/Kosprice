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
            ['name' => 'Bread 500g', 'category' => 'bakery', 'unit' => 'piece'],
            ['name' => 'Milk 1L', 'category' => 'dairy', 'unit' => 'liter'],
            ['name' => 'Eggs 10pcs', 'category' => 'dairy', 'unit' => 'pack'],
            ['name' => 'Rice 1kg', 'category' => 'grains', 'unit' => 'kg'],
            ['name' => 'Chicken 1kg', 'category' => 'meat', 'unit' => 'kg'],
            ['name' => 'Sunflower Oil 1L', 'category' => 'oil', 'unit' => 'liter'],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['name' => $product['name']],
                [
                    'category' => $product['category'],
                    'unit' => $product['unit'],
                ]
            );
        }
    }
}

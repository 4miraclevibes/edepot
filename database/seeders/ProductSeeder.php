<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'galon air 20 liter',
                'price' => 10000,
                'user_id' => 2,
            ],
            [
                'name' => 'galon air 10 liter',
                'price' => 5000,
                'user_id' => 2,
            ],
            [
                'name' => 'galon air 5 liter',
                'price' => 2500,
                'user_id' => 2,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}

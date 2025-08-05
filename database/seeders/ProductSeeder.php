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
                'name' => 'Refil Galon',
                'price' => 6000,
                'user_id' => 2,
            ],
            [
                'name' => 'Galon Baru',
                'price' => 5000,
                'user_id' => 2,
            ],
            [
                'name' => 'Galon Baru + Air',
                'price' => 50000,
                'user_id' => 2,
            ],
            [
                'name' => 'Refil Galon',
                'price' => 6000,
                'user_id' => 3,
            ],
            [
                'name' => 'Galon Baru',
                'price' => 5000,
                'user_id' => 3,
            ],
            [
                'name' => 'Galon Baru + Air',
                'price' => 50000,
                'user_id' => 3,
            ],
            [
                'name' => 'Refil Galon',
                'price' => 6000,
                'user_id' => 4,
            ],
            [
                'name' => 'Galon Baru',
                'price' => 5000,
                'user_id' => 4,
            ],
            [
                'name' => 'Galon Baru + Air',
                'price' => 50000,
                'user_id' => 4,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $products = [
            [
                'name' => 'Kopi Latte',
                'sku' => 'P001',
                'category' => 'Minuman',
                'price' => 32000,
                'stock' => 25,
            ],
            [
                'name' => 'Es Teh Tawar',
                'sku' => 'P002',
                'category' => 'Minuman',
                'price' => 8000,
                'stock' => 40,
            ],
            [
                'name' => 'Nasi Goreng Ayam',
                'sku' => 'P003',
                'category' => 'Makanan',
                'price' => 28000,
                'stock' => 15,
            ],
            [
                'name' => 'Roti Bakar Cokelat',
                'sku' => 'P004',
                'category' => 'Makanan',
                'price' => 18000,
                'stock' => 12,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}

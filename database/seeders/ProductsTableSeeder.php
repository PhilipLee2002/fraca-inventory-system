<?php

namespace Database\Seeders;
use App\Models\Product;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    
     # Run the database seeds.
     public function run()
    {
        $products = [
            [
                'name' => 'Laptop',
                'description' => '15-inch Business Laptop',
                'sku' => 'LP-001',
                'barcode' => '8901234567890',
                'category_id' => 1,
                'supplier_id' => 1,
                'cost_price' => 800.00,
                'selling_price' => 1200.00,
                'quantity' => 25,
                'reorder_level' => 5,
                'unit' => 'pcs'
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Bluetooth Wireless Mouse',
                'sku' => 'WM-002',
                'barcode' => '8901234567891',
                'category_id' => 1,
                'supplier_id' => 1,
                'cost_price' => 15.00,
                'selling_price' => 25.00,
                'quantity' => 100,
                'reorder_level' => 20,
                'unit' => 'pcs'
            ],
        ];
        
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}

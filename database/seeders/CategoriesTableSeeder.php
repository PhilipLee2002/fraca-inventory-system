<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    
     # Run the database seeds.
   public function run()
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices'],
            ['name' => 'Office Supplies', 'description' => 'Office materials'],
            ['name' => 'Food & Beverages', 'description' => 'Consumable items'],
            ['name' => 'Clothing', 'description' => 'Apparel and wearables'],
        ];
        
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

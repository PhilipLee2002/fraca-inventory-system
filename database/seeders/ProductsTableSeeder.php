<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = $this->catalog();
        $categories = Category::query()->pluck('id', 'slug');

        foreach ($catalog['products'] as $item) {
            $categoryId = $categories[$item['category_slug']] ?? null;

            if (! $categoryId) {
                throw new \RuntimeException('Missing category slug: '.$item['category_slug']);
            }

            Product::query()
                ->where('website_slug', $item['website_slug'])
                ->where('sku', '!=', $item['sku'])
                ->update(['website_slug' => null]);

            Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'name' => $item['name'],
                    'website_slug' => $item['website_slug'],
                    'description' => $item['description'],
                    'category_id' => $categoryId,
                    'supplier_id' => null,
                    'image' => $item['image'],
                    'is_in_house' => $item['is_in_house'] ?? true,
                    'is_active' => true,
                    'cost_price' => $item['cost_price'] ?? 0,
                    'selling_price' => $item['selling_price'] ?? 0,
                    'current_stock' => $item['current_stock'] ?? 0,
                    'reorder_level' => $item['reorder_level'] ?? 0,
                ]
            );
        }
    }

    private function catalog(): array
    {
        $path = database_path('data/website-catalog.json');
        $json = json_decode(file_get_contents($path), true);

        if (! is_array($json) || empty($json['products'])) {
            throw new \RuntimeException('website-catalog.json is missing or has no products. Run node database/scripts/extract-website-catalog.mjs');
        }

        return $json;
    }
}

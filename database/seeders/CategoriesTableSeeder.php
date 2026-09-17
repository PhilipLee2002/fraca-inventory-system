<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = $this->catalog();

        foreach ($catalog['categories'] as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'group' => $category['group'],
                    'description' => $category['description'] ?? null,
                ]
            );
        }
    }

    private function catalog(): array
    {
        $path = database_path('data/website-catalog.json');
        $json = json_decode(file_get_contents($path), true);

        if (! is_array($json) || empty($json['categories'])) {
            throw new \RuntimeException('website-catalog.json is missing or has no categories. Run node database/scripts/extract-website-catalog.mjs');
        }

        return $json;
    }
}

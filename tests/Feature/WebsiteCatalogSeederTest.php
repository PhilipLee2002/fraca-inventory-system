<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CategoriesTableSeeder;
use Database\Seeders\ProductsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_catalog_imports_fraca_products(): void
    {
        $this->seed([
            CategoriesTableSeeder::class,
            ProductsTableSeeder::class,
        ]);

        $this->assertSame(30, Category::count());
        $this->assertSame(273, Product::count());
        $this->assertTrue(Category::where('slug', 'beds')->where('group', 'Home Furniture')->exists());
        $this->assertTrue(Category::where('slug', 'bags')->where('group', 'Bags')->exists());

        $bed = Product::where('sku', 'FRC-BED-001')->first();
        $this->assertNotNull($bed);
        $this->assertTrue($bed->is_in_house);
        $this->assertNotEmpty($bed->image);
        $this->assertStringStartsWith('https://fracaservcom.co.ke/', $bed->image_url);

        $bag = Product::where('sku', 'like', 'FRC-BAG-%')->whereNotNull('selling_price')->where('selling_price', '>', 0)->first();
        $this->assertNotNull($bag);
        $this->assertGreaterThan(0, Product::where('description', 'like', '%Unpriced%')->count());
    }
}

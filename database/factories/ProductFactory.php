<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'sku' => $this->faker->unique()->ean8(),
            'description' => $this->faker->sentence(),
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'cost_price' => $this->faker->randomFloat(2, 5, 100),
            'selling_price' => $this->faker->randomFloat(2, 10, 200),
            'quantity' => $this->faker->numberBetween(0, 100),
            'reorder_level' => $this->faker->numberBetween(5, 20),
            'unit' => $this->faker->randomElement(['piece', 'kg', 'box']),
        ];
    }
}

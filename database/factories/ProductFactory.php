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
        $quantity = $this->faker->numberBetween(0, 100);
        
        return [
            'name' => $this->faker->word(),
            'sku' => $this->faker->unique()->ean8(),
            'barcode' => $this->faker->optional(0.7)->ean13(), // 70% chance of having a barcode
            'description' => $this->faker->sentence(),
            'category_id' => Category::factory(),
            'supplier_id' => Supplier::factory(),
            'cost_price' => $this->faker->randomFloat(2, 5, 100),
            'selling_price' => $this->faker->randomFloat(2, 10, 200),
            'current_stock' => $quantity,
            'initial_stock' => $quantity,
            'reorder_level' => $this->faker->numberBetween(5, 20),
            'minimum_stock' => $this->faker->numberBetween(1, 5),
            'unit_of_measurement' => $this->faker->randomElement(['piece', 'kg', 'box', 'liter']),
            'is_active' => true,
        ];
    }
}

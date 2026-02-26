<?php

namespace Database\Factories;

use App\Models\StockHistory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockHistory>
 */
class StockHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'quantity_change' => $this->faker->numberBetween(-50, 100),
            'transaction_type' => $this->faker->randomElement(['purchase', 'sale', 'adjustment']),
            'reference_id' => $this->faker->optional()->numberBetween(1, 1000),
            'notes' => $this->faker->optional()->text(),
        ];
    }
}

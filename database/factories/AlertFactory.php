<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alert>
 */
class AlertFactory extends Factory
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
            'alert_type' => $this->faker->randomElement(['low_stock', 'expiry_warning', 'reorder_point']),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'message' => $this->faker->sentence(),
            'is_resolved' => false,
            'resolved_at' => null,
        ];
    }
}

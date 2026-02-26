<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'user_id' => User::factory(),
            'purchase_number' => 'PUR-' . $this->faker->unique()->numberBetween(100000, 999999),
            'purchase_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'shipping_cost' => $this->faker->randomFloat(2, 0, 100),
            'tax_amount' => $this->faker->randomFloat(2, 0, 500),
            'discount_amount' => $this->faker->randomFloat(2, 0, 200),
            'payment_method' => $this->faker->randomElement(['cash', 'check', 'transfer']),
            'status' => $this->faker->randomElement(['pending', 'received', 'cancelled']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}

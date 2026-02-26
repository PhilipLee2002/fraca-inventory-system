<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'invoice_number' => 'INV-' . $this->faker->unique()->numberBetween(100000, 999999),
            'sale_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'total_amount' => $this->faker->randomFloat(2, 50, 5000),
            'shipping_cost' => $this->faker->randomFloat(2, 0, 50),
            'tax_amount' => $this->faker->randomFloat(2, 0, 250),
            'discount_amount' => $this->faker->randomFloat(2, 0, 100),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'transfer']),
            'payment_status' => $this->faker->randomElement(['paid', 'pending', 'overdue']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}

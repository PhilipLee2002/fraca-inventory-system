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
            'purchase_number' => 'PO-' . $this->faker->unique()->numberBetween(1000, 9999),
            'total_amount' => 0,
            'status' => $this->faker->randomElement(['pending', 'received', 'cancelled']),
            'notes' => $this->faker->sentence(),
        ];
    }
}

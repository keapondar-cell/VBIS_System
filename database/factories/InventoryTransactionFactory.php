<?php

namespace Database\Factories;

use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryTransaction>
 */
class InventoryTransactionFactory extends Factory
{
    protected $model = InventoryTransaction::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'user_id' => User::factory(),
            'department_id' => null,
            'transaction_type' => fake()->randomElement(['issue', 'return', 'adjustment']),
            'quantity' => fake()->numberBetween(1, 10),
            'notes' => fake()->sentence(),
        ];
    }
}

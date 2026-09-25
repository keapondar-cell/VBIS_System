<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'sku' => 'SKU-'.fake()->unique()->bothify('????-####'),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category' => fake()->word(),
            'quantity' => fake()->numberBetween(0, 100),
            'location' => fake()->word(),
            'status' => 'available',
            'qr_code' => null,
        ];
    }
}

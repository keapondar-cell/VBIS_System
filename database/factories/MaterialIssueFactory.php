<?php

namespace Database\Factories;

use App\Models\MaterialIssue;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialIssue>
 */
class MaterialIssueFactory extends Factory
{
    protected $model = MaterialIssue::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'issued_to_user_id' => User::factory(),
            'department_id' => null,
            'quantity' => fake()->numberBetween(1, 5),
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}

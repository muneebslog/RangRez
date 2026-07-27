<?php

namespace Database\Factories;

use App\Models\BudgetItem;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetItem>
 */
class BudgetItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'category' => fake()->randomElement(['Venue', 'Catering', 'Marketing', 'Staff', 'Misc']),
            'label' => fake()->words(3, true),
            'expected_amount' => fake()->randomFloat(2, 5000, 100000),
            'notes' => fake()->optional()->sentence(),
            'sort_order' => 0,
        ];
    }
}

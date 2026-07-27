<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
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
            'spent_by_user_id' => User::factory(),
            'category' => fake()->randomElement(['Venue', 'Catering', 'Marketing', 'Staff', 'Misc']),
            'description' => fake()->sentence(4),
            'amount' => fake()->randomFloat(2, 500, 50000),
            'receipt_path' => null,
            'spent_at' => now(),
        ];
    }
}

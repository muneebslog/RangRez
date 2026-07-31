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
            'spent_by_user_id' => null,
            'paid_by_company' => true,
            'category' => fake()->randomElement(['Venue', 'Catering', 'Marketing', 'Staff', 'Misc']),
            'description' => fake()->sentence(4),
            'amount' => fake()->randomFloat(2, 500, 50000),
            'receipt_path' => null,
            'spent_at' => now(),
            'repaid_at' => null,
        ];
    }

    public function companyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_by_company' => true,
            'spent_by_user_id' => null,
            'repaid_at' => null,
        ]);
    }

    public function personPaid(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_by_company' => false,
            'spent_by_user_id' => $user?->id ?? User::factory(),
            'repaid_at' => null,
        ]);
    }

    public function repaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_by_company' => false,
            'spent_by_user_id' => $attributes['spent_by_user_id'] ?? User::factory(),
            'repaid_at' => now(),
        ]);
    }
}

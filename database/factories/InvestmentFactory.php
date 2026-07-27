<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Investment>
 */
class InvestmentFactory extends Factory
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
            'user_id' => User::factory()->investor(),
            'amount' => fake()->randomFloat(2, 10000, 200000),
            'share_percent' => fake()->randomFloat(2, 5, 50),
            'notes' => fake()->optional()->sentence(),
            'proof_path' => null,
            'invested_at' => now(),
        ];
    }
}

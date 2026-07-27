<?php

namespace Database\Factories;

use App\Enums\PackageType;
use App\Models\Event;
use App\Models\TicketPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketPackage>
 */
class TicketPackageFactory extends Factory
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
            'name' => 'General Admission',
            'type' => PackageType::Normal,
            'price' => 2500,
            'capacity' => 100,
            'sales_start_at' => now()->subDay(),
            'sales_end_at' => now()->addMonth(),
            'is_active' => true,
        ];
    }

    public function earlyBird(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Early Bird',
            'type' => PackageType::EarlyBird,
            'price' => 1500,
            'sales_start_at' => now()->subWeek(),
            'sales_end_at' => now()->addDays(7),
        ]);
    }

    public function normal(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Normal',
            'type' => PackageType::Normal,
            'price' => 2500,
            'sales_start_at' => now()->addDays(7)->addSecond(),
            'sales_end_at' => now()->addMonths(2),
        ]);
    }
}

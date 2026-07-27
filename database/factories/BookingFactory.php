<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketPackage;
use App\Support\TicketCodeGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
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
            'ticket_package_id' => TicketPackage::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'ticket_code' => fn () => app(TicketCodeGenerator::class)->generate(),
            'payment_status' => PaymentStatus::Pending,
            'price' => 2500,
            'amount_paid' => 0,
            'payment_proof_path' => null,
            'checked_in_at' => null,
            'notes' => null,
            'created_by' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Booking $booking): void {
            if ($booking->ticket_package_id && ! $booking->event_id) {
                $booking->event_id = TicketPackage::find($booking->ticket_package_id)?->event_id;
            }

            if ($booking->event_id && ! $booking->ticket_package_id) {
                $package = TicketPackage::factory()->create(['event_id' => $booking->event_id]);
                $booking->ticket_package_id = $package->id;
                $booking->price = $package->price;
            }

            if ($booking->event_id && $booking->ticket_package_id) {
                $package = TicketPackage::find($booking->ticket_package_id);

                if ($package && (int) $package->event_id !== (int) $booking->event_id) {
                    $package = TicketPackage::factory()->create(['event_id' => $booking->event_id]);
                    $booking->ticket_package_id = $package->id;
                    $booking->price = $package->price;
                }
            }
        });
    }

    public function forEvent(Event $event, ?TicketPackage $package = null): static
    {
        $package ??= TicketPackage::factory()->for($event)->create();

        return $this->state(fn (array $attributes) => [
            'event_id' => $event->id,
            'ticket_package_id' => $package->id,
            'price' => $package->price,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Paid,
            'amount_paid' => $attributes['price'] ?? 2500,
        ]);
    }

    public function checkedIn(): static
    {
        return $this->paid()->state(fn (array $attributes) => [
            'checked_in_at' => now(),
        ]);
    }
}

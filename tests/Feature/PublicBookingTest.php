<?php

use App\Enums\PaymentStatus;
use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guests can view a published event page', function () {
    $event = Event::factory()->published()->create([
        'title' => 'Public Fest',
    ]);

    TicketPackage::factory()->earlyBird()->for($event)->create();

    $this->get(route('public.events.show', $event))
        ->assertOk()
        ->assertSee('Public Fest');
});

test('guests can book an event and receive confirmation email', function () {
    Mail::fake();

    $event = Event::factory()->published()->create();
    TicketPackage::factory()->earlyBird()->for($event)->create([
        'price' => 1500,
        'sales_start_at' => now()->subDay(),
        'sales_end_at' => now()->addWeek(),
    ]);

    Livewire::test('pages::public.event-book', ['event' => $event])
        ->set('name', 'Sara Ali')
        ->set('email', 'sara@example.com')
        ->set('phone', '03001234567')
        ->call('book')
        ->assertHasNoErrors()
        ->assertRedirect();

    $booking = Booking::query()->first();

    expect($booking)->not->toBeNull()
        ->and($booking->name)->toBe('Sara Ali')
        ->and($booking->payment_status)->toBe(PaymentStatus::Pending)
        ->and($booking->price)->toEqual('1500.00');

    Mail::assertQueued(BookingConfirmed::class, fn (BookingConfirmed $mail) => $mail->booking->is($booking));
});

test('guest can upload payment proof on booking status page', function () {
    Storage::fake('public');

    $event = Event::factory()->published()->create();
    $package = TicketPackage::factory()->earlyBird()->for($event)->create();
    $booking = Booking::factory()->forEvent($event, $package)->create();

    Livewire::test('pages::public.booking-status', ['booking' => $booking])
        ->set('payment_proof', UploadedFile::fake()->image('proof.jpg'))
        ->call('uploadProof')
        ->assertHasNoErrors();

    $booking->refresh();

    expect($booking->payment_proof_path)->not->toBeNull();
    Storage::disk('public')->assertExists($booking->payment_proof_path);
});

test('draft events are not publicly bookable', function () {
    $event = Event::factory()->create(['status' => \App\Enums\EventStatus::Draft]);

    $this->get(route('public.events.show', $event))->assertNotFound();
});

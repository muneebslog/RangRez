<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\TicketPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('check-in staff can mark a paid guest as arrived', function () {
    $staff = User::factory()->checkIn()->create();
    $event = Event::factory()->live()->create();
    $package = TicketPackage::factory()->earlyBird()->for($event)->create();
    $booking = Booking::factory()->forEvent($event, $package)->paid()->create([
        'name' => 'Guest One',
    ]);

    Livewire::actingAs($staff)
        ->test('pages::events.check-in', ['event' => $event])
        ->set('search', 'Guest One')
        ->call('checkIn', $booking->id)
        ->assertHasNoErrors();

    expect($booking->fresh()->isCheckedIn())->toBeTrue();
});

test('pending guests cannot check in without admin override', function () {
    $staff = User::factory()->checkIn()->create();
    $event = Event::factory()->live()->create();
    $package = TicketPackage::factory()->earlyBird()->for($event)->create();
    $booking = Booking::factory()->forEvent($event, $package)->create();

    Livewire::actingAs($staff)
        ->test('pages::events.check-in', ['event' => $event])
        ->call('checkIn', $booking->id);

    expect($booking->fresh()->isCheckedIn())->toBeFalse();
});

test('admin can force check-in a pending guest', function () {
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->live()->create();
    $package = TicketPackage::factory()->earlyBird()->for($event)->create();
    $booking = Booking::factory()->forEvent($event, $package)->create();

    Livewire::actingAs($admin)
        ->test('pages::events.check-in', ['event' => $event])
        ->call('checkIn', $booking->id);

    expect($booking->fresh()->isCheckedIn())->toBeTrue();
});

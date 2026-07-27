<?php

use App\Models\Event;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('investor can view their investments portal', function () {
    $investor = User::factory()->investor()->create();
    $event = Event::factory()->published()->create();

    Investment::factory()->create([
        'event_id' => $event->id,
        'user_id' => $investor->id,
        'amount' => 50000,
    ]);

    $this->actingAs($investor)
        ->get(route('investments.index'))
        ->assertOk()
        ->assertSee($event->title)
        ->assertSee('50,000');
});

test('investor can view event they invested in', function () {
    $investor = User::factory()->investor()->create();
    $event = Event::factory()->published()->create();

    Investment::factory()->create([
        'event_id' => $event->id,
        'user_id' => $investor->id,
    ]);

    $this->actingAs($investor)
        ->get(route('events.show', $event))
        ->assertOk();
});

test('investor cannot view unrelated events', function () {
    $investor = User::factory()->investor()->create();
    $event = Event::factory()->published()->create();

    $this->actingAs($investor)
        ->get(route('events.show', $event))
        ->assertForbidden();
});

test('finance can export bookings csv', function () {
    $finance = User::factory()->finance()->create();
    $event = Event::factory()->published()->create();

    $this->actingAs($finance)
        ->get(route('events.bookings.export', $event))
        ->assertOk()
        ->assertHeader('content-disposition');
});

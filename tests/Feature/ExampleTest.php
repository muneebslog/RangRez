<?php

use App\Enums\EventStatus;
use App\Models\Event;

test('home page introduces RangRez', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('RangRez')
        ->assertSee('Event operations')
        ->assertSee('Team login');
});

test('home page lists published upcoming events', function () {
    Event::factory()->published()->create([
        'title' => 'Color Night Live',
        'status' => EventStatus::Published,
        'starts_at' => now()->addWeek(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Color Night Live')
        ->assertSee('Upcoming events');
});

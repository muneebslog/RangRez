<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('events index displays events as cards', function () {
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->published()->create([
        'title' => 'Paint Night Cards',
        'venue' => 'Studio One',
        'capacity' => 40,
    ]);

    $this->actingAs($admin)
        ->get(route('events.index'))
        ->assertOk()
        ->assertSee('Paint Night Cards')
        ->assertSee('Studio One')
        ->assertSee('Capacity 40')
        ->assertSee('data-flux-card', false)
        ->assertDontSee('<table', false);
});

test('events index search still filters card results', function () {
    $admin = User::factory()->admin()->create();

    Event::factory()->published()->create(['title' => 'Color Splash']);
    Event::factory()->published()->create(['title' => 'Quiet Gallery']);

    $this->actingAs($admin);

    Livewire\Livewire::test('pages::events.index')
        ->set('search', 'Splash')
        ->assertSee('Color Splash')
        ->assertDontSee('Quiet Gallery');
});

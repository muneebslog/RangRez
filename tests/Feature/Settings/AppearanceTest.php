<?php

use App\Models\User;

test('appearance settings page shows color theme options', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('appearance.edit'))
        ->assertOk()
        ->assertSee('Color theme')
        ->assertSee('Splash')
        ->assertSee('Festival')
        ->assertSee('Ink')
        ->assertSee('Light')
        ->assertSee('Dark')
        ->assertSee('System');
});

test('home page includes theme switcher and apply script', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('rangrez.theme', false)
        ->assertSee('RangRez.applyTheme', false)
        ->assertSee('Splash')
        ->assertSee('Festival')
        ->assertSee('Ink');
});

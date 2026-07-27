<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view events index', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('events.index'))
        ->assertOk();
});

test('check-in staff cannot create events', function () {
    $user = User::factory()->checkIn()->create();

    $this->actingAs($user)
        ->get(route('events.create'))
        ->assertForbidden();
});

test('investor cannot manage finance pages without investment', function () {
    $investor = User::factory()->investor()->create();
    $event = Event::factory()->published()->create();

    $this->actingAs($investor)
        ->get(route('events.budget', $event))
        ->assertForbidden();
});

test('finance user can access budget page', function () {
    $finance = User::factory()->finance()->create();
    $event = Event::factory()->published()->create();

    $this->actingAs($finance)
        ->get(route('events.budget', $event))
        ->assertOk();
});

test('user role helpers match enum', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->role)->toBe(UserRole::Admin)
        ->and($admin->canManageEvents())->toBeTrue()
        ->and($admin->canManageFinance())->toBeTrue()
        ->and($admin->canCheckIn())->toBeTrue();
});

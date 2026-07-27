<?php

use App\Enums\EventStatus;
use App\Enums\PackageType;
use App\Models\Event;
use App\Models\TicketPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can create an event with early bird and normal packages', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test('pages::events.create')
        ->set('title', 'Color Fest')
        ->set('description', 'A fun event')
        ->set('venue', 'City Hall')
        ->set('starts_at', now()->addMonth()->format('Y-m-d\TH:i'))
        ->set('ends_at', now()->addMonth()->addHours(4)->format('Y-m-d\TH:i'))
        ->set('capacity', 200)
        ->set('status', EventStatus::Published->value)
        ->set('currency', 'PKR')
        ->set('early_bird_price', '1200')
        ->set('normal_price', '2000')
        ->set('early_bird_ends_at', now()->addDays(5)->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    $event = Event::query()->where('title', 'Color Fest')->first();

    expect($event)->not->toBeNull()
        ->and($event->ticketPackages)->toHaveCount(2)
        ->and($event->ticketPackages->pluck('type')->all())->toContain(PackageType::EarlyBird, PackageType::Normal);
});

test('current package returns early bird while on sale', function () {
    $event = Event::factory()->published()->create();

    TicketPackage::factory()->earlyBird()->for($event)->create([
        'sales_start_at' => now()->subDay(),
        'sales_end_at' => now()->addDays(3),
    ]);

    TicketPackage::factory()->normal()->for($event)->create([
        'sales_start_at' => now()->addDays(3)->addSecond(),
        'sales_end_at' => $event->starts_at,
    ]);

    expect($event->currentPackage()?->type)->toBe(PackageType::EarlyBird);
});

test('admin can view event overview', function () {
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->published()->create();

    $this->actingAs($admin)
        ->get(route('events.show', $event))
        ->assertOk()
        ->assertSee($event->title);
});

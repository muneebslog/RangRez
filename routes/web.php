<?php

use App\Http\Controllers\BookingExportController;
use App\Models\Event;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $upcomingEvents = Event::query()
        ->bookable()
        ->orderBy('starts_at')
        ->limit(6)
        ->get();

    return view('welcome', [
        'upcomingEvents' => $upcomingEvents,
        'featuredEvent' => $upcomingEvents->first(),
    ]);
})->name('home');

Route::livewire('events/{event:slug}', 'pages::public.event-show')->name('public.events.show');
Route::livewire('events/{event:slug}/book', 'pages::public.event-book')->name('public.events.book');
Route::livewire('bookings/{booking:ticket_code}', 'pages::public.booking-status')->name('bookings.status');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    Route::livewire('manage/events', 'pages::events.index')->name('events.index');
    Route::livewire('manage/events/create', 'pages::events.create')->name('events.create');
    Route::livewire('manage/events/{event}/edit', 'pages::events.edit')->name('events.edit');
    Route::livewire('manage/events/{event}', 'pages::events.show')->name('events.show');
    Route::livewire('manage/events/{event}/packages', 'pages::events.packages')->name('events.packages');
    Route::livewire('manage/events/{event}/budget', 'pages::events.budget')->name('events.budget');
    Route::livewire('manage/events/{event}/investments', 'pages::events.investments')->name('events.investments');
    Route::livewire('manage/events/{event}/expenses', 'pages::events.expenses')->name('events.expenses');
    Route::livewire('manage/events/{event}/bookings', 'pages::events.bookings')->name('events.bookings');
    Route::livewire('manage/events/{event}/check-in', 'pages::events.check-in')->name('events.check-in');
    Route::get('manage/events/{event}/bookings/export', BookingExportController::class)->name('events.bookings.export');

    Route::livewire('my-investments', 'pages::investments.index')->name('investments.index');
});

require __DIR__.'/settings.php';


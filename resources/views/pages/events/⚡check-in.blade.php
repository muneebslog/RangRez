<?php

use App\Models\Event;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Check-in')] class extends Component
{
    public Event $event;

    #[Url]
    public string $search = '';

    public function mount(Event $event): void
    {
        $this->authorize('checkIn', $event);
        $this->event = $event;
    }

    public function checkIn(int $bookingId): void
    {
        $this->authorize('checkIn', $this->event);

        $booking = $this->event->bookings()->findOrFail($bookingId);

        if (! $booking->canCheckIn() && ! auth()->user()->isAdmin()) {
            Flux::toast(variant: 'danger', text: __('Guest must be paid (or waived) before check-in.'));

            return;
        }

        if ($booking->isCheckedIn()) {
            Flux::toast(variant: 'warning', text: __('Already checked in.'));

            return;
        }

        $booking->checkIn();
        Flux::toast(variant: 'success', text: __('Checked in :name.', ['name' => $booking->name]));
    }

    public function undoCheckIn(int $bookingId): void
    {
        $this->authorize('checkIn', $this->event);

        $booking = $this->event->bookings()->findOrFail($bookingId);
        $booking->undoCheckIn();

        Flux::toast(variant: 'success', text: __('Check-in undone.'));
    }

    #[Computed]
    public function bookings()
    {
        return $this->event->bookings()
            ->with('ticketPackage')
            ->when($this->search, function ($query): void {
                $query->where(function ($q): void {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('ticket_code', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => $this->event->bookings()->count(),
            'checked_in' => $this->event->bookings()->whereNotNull('checked_in_at')->count(),
        ];
    }
}; ?>

<div class="space-y-6">
    <x-event-hub-nav :event="$event" />

    <div class="flex flex-wrap items-center justify-between gap-3">
        <flux:heading size="md">
            {{ __('Checked in') }}: {{ $this->stats['checked_in'] }} / {{ $this->stats['total'] }}
        </flux:heading>
        <div class="min-w-72 flex-1">
            <flux:input wire:model.live.debounce.200ms="search" :placeholder="__('Search name or ticket code...')" icon="magnifying-glass" autofocus />
        </div>
    </div>

    <div class="space-y-2">
        @forelse ($this->bookings as $booking)
            <div
                class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
                wire:key="checkin-{{ $booking->id }}"
            >
                <div>
                    <flux:heading size="md">{{ $booking->name }}</flux:heading>
                    <flux:text class="mt-1">
                        <span class="font-mono">{{ $booking->ticket_code }}</span>
                        · {{ $booking->payment_status->label() }}
                        · {{ $booking->ticketPackage->name }}
                        @if ($booking->isCheckedIn())
                            · {{ __('In at') }} {{ $booking->checked_in_at->format('g:i A') }}
                        @endif
                    </flux:text>
                </div>

                <div>
                    @if ($booking->isCheckedIn())
                        <flux:button variant="ghost" wire:click="undoCheckIn({{ $booking->id }})">{{ __('Undo') }}</flux:button>
                    @else
                        <flux:button
                            variant="primary"
                            wire:click="checkIn({{ $booking->id }})"
                            :disabled="! $booking->canCheckIn() && ! auth()->user()->isAdmin()"
                        >
                            {{ __('Check in') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        @empty
            <flux:text>{{ $search ? __('No matching guests.') : __('No bookings yet.') }}</flux:text>
        @endforelse
    </div>
</div>

<?php

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Event;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Bookings')] class extends Component
{
    use WithPagination;

    public Event $event;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function mount(Event $event): void
    {
        $this->authorize('manageBookings', $event);
        $this->event = $event;
    }

    public function markPaid(int $bookingId): void
    {
        $this->authorize('manageBookings', $this->event);

        $booking = $this->event->bookings()->findOrFail($bookingId);
        $booking->markPaid();

        Flux::toast(variant: 'success', text: __('Marked as paid.'));
    }

    public function markPending(int $bookingId): void
    {
        $this->authorize('manageBookings', $this->event);

        $booking = $this->event->bookings()->findOrFail($bookingId);
        $booking->update([
            'payment_status' => PaymentStatus::Pending,
            'amount_paid' => 0,
        ]);

        Flux::toast(variant: 'success', text: __('Marked as pending.'));
    }

    public function waive(int $bookingId): void
    {
        $this->authorize('manageBookings', $this->event);

        $this->event->bookings()->whereKey($bookingId)->update([
            'payment_status' => PaymentStatus::Waived,
            'amount_paid' => 0,
        ]);

        Flux::toast(variant: 'success', text: __('Payment waived.'));
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
                        ->orWhere('ticket_code', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status, fn ($q) => $q->where('payment_status', $this->status))
            ->latest()
            ->paginate(15);
    }
}; ?>

<div class="space-y-6">
    <x-event-hub-nav :event="$event" />

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div class="flex flex-1 flex-wrap gap-3">
            <div class="min-w-56 flex-1">
                <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search name, email, ticket...')" icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="status" class="w-40">
                <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
                @foreach (App\Enums\PaymentStatus::cases() as $paymentStatus)
                    <flux:select.option value="{{ $paymentStatus->value }}">{{ $paymentStatus->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <flux:button :href="route('events.bookings.export', $event)" variant="ghost" icon="arrow-down-tray">
            {{ __('Export CSV') }}
        </flux:button>
    </div>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-4 py-3 text-start">{{ __('Guest') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Ticket') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Package') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Payment') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Proof') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->bookings as $booking)
                    <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="booking-{{ $booking->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $booking->name }}</div>
                            <div class="text-zinc-500">{{ $booking->email }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono">{{ $booking->ticket_code }}</td>
                        <td class="px-4 py-3">
                            {{ $booking->ticketPackage->name }}
                            <div class="text-zinc-500">{{ $event->currency }} {{ number_format((float) $booking->price, 2) }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $booking->payment_status->label() }}</td>
                        <td class="px-4 py-3">
                            @if ($booking->paymentProofUrl())
                                <a href="{{ $booking->paymentProofUrl() }}" target="_blank" class="underline">{{ __('View') }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @if ($booking->payment_status !== App\Enums\PaymentStatus::Paid)
                                    <flux:button size="sm" wire:click="markPaid({{ $booking->id }})">{{ __('Mark paid') }}</flux:button>
                                @else
                                    <flux:button size="sm" variant="ghost" wire:click="markPending({{ $booking->id }})">{{ __('Undo paid') }}</flux:button>
                                @endif
                                @if ($booking->payment_status !== App\Enums\PaymentStatus::Waived)
                                    <flux:button size="sm" variant="ghost" wire:click="waive({{ $booking->id }})">{{ __('Waive') }}</flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-zinc-500">{{ __('No bookings found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $this->bookings->links() }}
</div>

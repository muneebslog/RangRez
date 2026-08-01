<?php

use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Services\EventFinancialSummary;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Dashboard')] class extends Component
{
    #[Computed]
    public function events()
    {
        return Event::query()
            ->latest('starts_at')
            ->withCount([
                'bookings',
                'bookings as paid_bookings_count' => fn ($q) => $q->where('payment_status', PaymentStatus::Paid),
                'bookings as checked_in_count' => fn ($q) => $q->whereNotNull('checked_in_at'),
            ])->get();
    }

    #[Computed]
    public function companyTotals(): array
    {
        $summary = app(EventFinancialSummary::class);
        $totals = [
            'events' => $this->events->count(),
            'booked' => 0,
            'revenue' => '0.00',
            'expenses' => '0.00',
            'owed' => '0.00',
        ];

        foreach ($this->events as $event) {
            $stats = $summary->for($event);
            $totals['booked'] += $stats['booked'];
            $totals['revenue'] = bcadd($totals['revenue'], $stats['revenue'], 2);
            $totals['expenses'] = bcadd($totals['expenses'], $stats['expenses'], 2);
            $totals['owed'] = bcadd($totals['owed'], $stats['owed'], 2);
        }

        return $totals;
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
            <flux:text>{{ __('Overview of your events and finances.') }}</flux:text>
        </div>

        @if (auth()->user()->canManageEvents())
            <flux:button :href="route('events.create')" wire:navigate variant="primary" icon="plus">
                {{ __('New event') }}
            </flux:button>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('Events') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $this->companyTotals['events'] }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('Bookings') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $this->companyTotals['booked'] }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('Revenue') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ number_format((float) $this->companyTotals['revenue'], 2) }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('To repay') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ number_format((float) $this->companyTotals['owed'], 2) }}</flux:heading>
        </div>
    </div>

    <div class="space-y-3">
        <flux:heading size="lg">{{ __('Events') }}</flux:heading>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($this->events as $event)
                <a
                    href="{{ route('events.show', $event) }}"
                    wire:navigate
                    class="group block h-full transition"
                >
                    <flux:card class="h-full transition group-hover:border-[var(--rr-accent)] group-hover:ring-1 group-hover:ring-[var(--rr-accent)]/30">
                        <div class="flex h-full flex-col gap-3">
                            <div class="flex items-start justify-between gap-3">
                                <flux:heading size="md" class="group-hover:text-[var(--rr-accent)]">{{ $event->title }}</flux:heading>
                                <flux:badge size="sm">{{ $event->status->label() }}</flux:badge>
                            </div>
                            <flux:text>
                                {{ $event->starts_at->format('M j, Y') }}
                            </flux:text>
                            <flux:text class="text-sm">
                                {{ $event->bookings_count }} {{ __('booked') }}
                                · {{ $event->paid_bookings_count }} {{ __('paid') }}
                                · {{ $event->checked_in_count }} {{ __('checked in') }}
                            </flux:text>
                        </div>
                    </flux:card>
                </a>
            @empty
                <flux:text class="sm:col-span-2 xl:col-span-3">{{ __('No events yet.') }}</flux:text>
            @endforelse
        </div>
    </div>
</div>

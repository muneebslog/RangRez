<?php

use App\Models\Event;
use App\Services\EventFinancialSummary;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Event overview')] class extends Component
{
    public Event $event;

    public function mount(Event $event): void
    {
        $this->authorize('view', $event);
        $this->event = $event;
    }

    #[Computed]
    public function summary(): array
    {
        return app(EventFinancialSummary::class)->for($this->event);
    }

    #[Computed]
    public function publicUrl(): string
    {
        return route('public.events.show', $this->event);
    }
}; ?>

<div class="space-y-6">
    <x-event-hub-nav :event="$event" />

    <div class="flex flex-wrap items-center gap-3">
        <flux:button :href="$this->publicUrl" target="_blank" variant="ghost" size="sm" icon="arrow-top-right-on-square">
            {{ __('Public page') }}
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('Booked') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $this->summary['booked'] }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('Paid') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $this->summary['paid'] }}</flux:heading>
            <flux:text class="mt-1">{{ $this->summary['pending'] }} {{ __('pending') }}</flux:text>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('Checked in') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $this->summary['checked_in'] }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('Revenue') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $event->currency }} {{ number_format((float) $this->summary['revenue'], 2) }}</flux:heading>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('To repay') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $event->currency }} {{ number_format((float) $this->summary['owed'], 2) }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('Expenses') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $event->currency }} {{ number_format((float) $this->summary['expenses'], 2) }}</flux:heading>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('Budget') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $event->currency }} {{ number_format((float) $this->summary['budget'], 2) }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Variance') }}: {{ $event->currency }} {{ number_format((float) $this->summary['budget_variance'], 2) }}
            </flux:text>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text>{{ __('Profit / Loss') }}</flux:text>
            <flux:heading size="lg" class="mt-1">{{ $event->currency }} {{ number_format((float) $this->summary['profit_loss'], 2) }}</flux:heading>
        </div>
    </div>

    @if ($event->description)
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="md">{{ __('Description') }}</flux:heading>
            <flux:text class="mt-2 whitespace-pre-line">{{ $event->description }}</flux:text>
        </div>
    @endif
</div>

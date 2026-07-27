<?php

use App\Models\Event;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public')] #[Title('Event')] class extends Component
{
    public Event $event;

    public function mount(Event $event): void
    {
        abort_unless($event->isBookable(), 404);

        $this->event = $event->load('ticketPackages');
    }

    #[Computed]
    public function currentPackage()
    {
        return $this->event->currentPackage();
    }
}; ?>

<div class="space-y-8">
    <div class="space-y-3">
        <flux:heading size="xl">{{ $event->title }}</flux:heading>
        <flux:text>
            {{ $event->starts_at->format('l, M j, Y · g:i A') }}
            @if ($event->venue)
                · {{ $event->venue }}
            @endif
        </flux:text>
        @if ($event->description)
            <flux:text class="whitespace-pre-line">{{ $event->description }}</flux:text>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        @if ($this->currentPackage)
            <flux:heading size="lg">{{ $this->currentPackage->name }}</flux:heading>
            <flux:text class="mt-2">
                {{ $this->currentPackage->type->label() }}
                · {{ $event->currency }} {{ number_format((float) $this->currentPackage->price, 2) }}
            </flux:text>

            @if ($event->isSoldOut() || $this->currentPackage->isSoldOut())
                <flux:text class="mt-4 font-medium">{{ __('Sold out') }}</flux:text>
            @else
                <div class="mt-4">
                    <flux:button :href="route('public.events.book', $event)" wire:navigate variant="primary">
                        {{ __('Register now') }}
                    </flux:button>
                </div>
            @endif
        @else
            <flux:text>{{ __('Ticket sales are not open right now.') }}</flux:text>
        @endif
    </div>

    <div class="space-y-2">
        <flux:heading size="md">{{ __('Packages') }}</flux:heading>
        @foreach ($event->ticketPackages->where('is_active', true) as $package)
            <div class="rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <div class="font-medium">{{ $package->name }} — {{ $event->currency }} {{ number_format((float) $package->price, 2) }}</div>
                <flux:text>
                    {{ $package->type->label() }}
                    @if ($package->sales_end_at)
                        · {{ __('until') }} {{ $package->sales_end_at->format('M j, Y g:i A') }}
                    @endif
                    @if ($package->isOnSale())
                        · {{ __('Available now') }}
                    @endif
                </flux:text>
            </div>
        @endforeach
    </div>
</div>

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
        <p class="text-sm uppercase tracking-[0.25em] text-rr-accent">{{ __('Event') }}</p>
        <h1 class="font-serif text-4xl tracking-tight text-rr-fg sm:text-5xl">{{ $event->title }}</h1>
        <p class="text-rr-muted">
            {{ $event->starts_at->format('l, M j, Y · g:i A') }}
            @if ($event->venue)
                · {{ $event->venue }}
            @endif
        </p>
        @if ($event->description)
            <p class="whitespace-pre-line text-rr-muted">{{ $event->description }}</p>
        @endif
    </div>

    <div class="rr-surface rounded-xl p-6">
        @if ($this->currentPackage)
            <h2 class="text-xl font-semibold text-rr-fg">{{ $this->currentPackage->name }}</h2>
            <p class="mt-2 text-rr-muted">
                {{ $this->currentPackage->type->label() }}
                · {{ $event->currency }} {{ number_format((float) $this->currentPackage->price, 2) }}
            </p>

            @if ($event->isSoldOut() || $this->currentPackage->isSoldOut())
                <p class="mt-4 font-medium text-rr-fg">{{ __('Sold out') }}</p>
            @else
                <div class="mt-4">
                    <a
                        href="{{ route('public.events.book', $event) }}"
                        wire:navigate
                        class="rr-cta inline-flex rounded-md px-5 py-2.5 text-sm font-semibold transition"
                    >
                        {{ __('Register now') }}
                    </a>
                </div>
            @endif
        @else
            <p class="text-rr-muted">{{ __('Ticket sales are not open right now.') }}</p>
        @endif
    </div>

    <div class="space-y-3">
        <h2 class="text-lg font-semibold text-rr-fg">{{ __('Packages') }}</h2>
        @foreach ($event->ticketPackages->where('is_active', true) as $package)
            <div class="rr-surface rounded-lg px-4 py-3">
                <div class="font-medium text-rr-fg">{{ $package->name }} — {{ $event->currency }} {{ number_format((float) $package->price, 2) }}</div>
                <p class="text-sm text-rr-muted">
                    {{ $package->type->label() }}
                    @if ($package->sales_end_at)
                        · {{ __('until') }} {{ $package->sales_end_at->format('M j, Y g:i A') }}
                    @endif
                    @if ($package->isOnSale())
                        · <span class="text-rr-accent">{{ __('Available now') }}</span>
                    @endif
                </p>
            </div>
        @endforeach
    </div>
</div>

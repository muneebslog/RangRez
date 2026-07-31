@props(['event'])

@php
    $tabs = [
        ['label' => __('Overview'), 'route' => 'events.show', 'show' => true],
        ['label' => __('Packages'), 'route' => 'events.packages', 'show' => auth()->user()->canManageEvents()],
        ['label' => __('Budget'), 'route' => 'events.budget', 'show' => auth()->user()->canManageFinance()],
        ['label' => __('Expenses'), 'route' => 'events.expenses', 'show' => auth()->user()->canManageFinance()],
        ['label' => __('Bookings'), 'route' => 'events.bookings', 'show' => auth()->user()->canManageFinance() || auth()->user()->canManageEvents()],
        ['label' => __('Check-in'), 'route' => 'events.check-in', 'show' => auth()->user()->canCheckIn()],
    ];
@endphp

<div class="mb-6 space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ $event->title }}</flux:heading>
            <flux:text class="mt-1">
                {{ $event->starts_at->format('M j, Y g:i A') }}
                @if ($event->venue)
                    · {{ $event->venue }}
                @endif
                · {{ $event->status->label() }}
            </flux:text>
        </div>

        @can('update', $event)
            <flux:button :href="route('events.edit', $event)" wire:navigate variant="ghost" size="sm">
                {{ __('Edit event') }}
            </flux:button>
        @endcan
    </div>

    <flux:navbar>
        @foreach ($tabs as $tab)
            @if ($tab['show'])
                <flux:navbar.item
                    :href="route($tab['route'], $event)"
                    :current="request()->routeIs($tab['route'])"
                    wire:navigate
                >
                    {{ $tab['label'] }}
                </flux:navbar.item>
            @endif
        @endforeach
    </flux:navbar>
</div>

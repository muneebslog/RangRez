<?php

use App\Models\Event;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Events')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Event::class);
    }

    #[Computed]
    public function events()
    {
        return Event::query()
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->latest('starts_at')
            ->paginate(12);
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Events') }}</flux:heading>
            <flux:text>{{ __('Manage all company events.') }}</flux:text>
        </div>

        @can('create', App\Models\Event::class)
            <flux:button :href="route('events.create')" wire:navigate variant="primary" icon="plus">
                {{ __('New event') }}
            </flux:button>
        @endcan
    </div>

    <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search events...')" icon="magnifying-glass" />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->events as $event)
            <a
                href="{{ route('events.show', $event) }}"
                wire:navigate
                wire:key="event-{{ $event->id }}"
                class="group block transition"
            >
                <flux:card class="h-full transition group-hover:border-[var(--rr-accent)] group-hover:ring-1 group-hover:ring-[var(--rr-accent)]/30">
                    <div class="flex h-full flex-col gap-4">
                        <div class="flex items-start justify-between gap-3">
                            <flux:heading size="md" class="group-hover:text-[var(--rr-accent)]">
                                {{ $event->title }}
                            </flux:heading>
                            <flux:badge
                                size="sm"
                                :color="match ($event->status) {
                                    App\Enums\EventStatus::Draft => 'zinc',
                                    App\Enums\EventStatus::Published => 'sky',
                                    App\Enums\EventStatus::Live => 'lime',
                                    App\Enums\EventStatus::Closed => 'zinc',
                                }"
                            >
                                {{ $event->status->label() }}
                            </flux:badge>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
                                <flux:icon.calendar-days class="size-4 shrink-0" />
                                <span>{{ $event->starts_at->format('M j, Y · g:i A') }}</span>
                            </div>

                            @if ($event->venue)
                                <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
                                    <flux:icon.map-pin class="size-4 shrink-0" />
                                    <span class="truncate">{{ $event->venue }}</span>
                                </div>
                            @endif

                            <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
                                <flux:icon.users class="size-4 shrink-0" />
                                <span>{{ $event->capacity ? __('Capacity :count', ['count' => $event->capacity]) : __('Unlimited capacity') }}</span>
                            </div>
                        </div>

                        <div class="mt-auto pt-1 text-sm font-medium text-[var(--rr-accent)] opacity-0 transition group-hover:opacity-100">
                            {{ __('Open event') }} →
                        </div>
                    </div>
                </flux:card>
            </a>
        @empty
            <flux:card class="sm:col-span-2 xl:col-span-3">
                <div class="py-8 text-center">
                    <flux:text>{{ __('No events found.') }}</flux:text>
                </div>
            </flux:card>
        @endforelse
    </div>

    <div>
        {{ $this->events->links() }}
    </div>
</div>

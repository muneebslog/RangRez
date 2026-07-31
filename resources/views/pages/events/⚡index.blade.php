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
            ->paginate(10);
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

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-start text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Title') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Date') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Capacity') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->events as $event)
                    <tr class="border-b border-zinc-100 dark:border-zinc-800" wire:key="event-{{ $event->id }}">
                        <td class="px-4 py-3">
                            <a href="{{ route('events.show', $event) }}" class="font-medium hover:underline" wire:navigate>
                                {{ $event->title }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $event->starts_at->format('M j, Y g:i A') }}</td>
                        <td class="px-4 py-3">{{ $event->status->label() }}</td>
                        <td class="px-4 py-3">{{ $event->capacity ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-zinc-500">{{ __('No events found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $this->events->links() }}
    </div>
</div>

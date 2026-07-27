<?php

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Edit event')] class extends Component
{
    public Event $event;

    public string $title = '';

    public string $description = '';

    public string $venue = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public ?int $capacity = null;

    public string $status = 'draft';

    public string $currency = 'PKR';

    public function mount(Event $event): void
    {
        $this->authorize('update', $event);

        $this->event = $event;
        $this->title = $event->title;
        $this->description = (string) $event->description;
        $this->venue = (string) $event->venue;
        $this->starts_at = $event->starts_at->format('Y-m-d\TH:i');
        $this->ends_at = $event->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->capacity = $event->capacity;
        $this->status = $event->status->value;
        $this->currency = $event->currency;
    }

    public function save(): void
    {
        $this->authorize('update', $this->event);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', Rule::enum(EventStatus::class)],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $this->event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'venue' => $validated['venue'] ?: null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?: null,
            'capacity' => $validated['capacity'],
            'status' => $validated['status'],
            'currency' => strtoupper($validated['currency']),
        ]);

        $this->redirect(route('events.show', $this->event), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Edit event') }}</flux:heading>
        <flux:text>{{ $event->title }}</flux:text>
    </div>

    <form wire:submit="save" class="space-y-4">
        <flux:input wire:model="title" :label="__('Title')" required />
        <flux:textarea wire:model="description" :label="__('Description')" rows="4" />
        <flux:input wire:model="venue" :label="__('Venue')" />

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input wire:model="starts_at" type="datetime-local" :label="__('Starts at')" required />
            <flux:input wire:model="ends_at" type="datetime-local" :label="__('Ends at')" />
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:input wire:model="capacity" type="number" :label="__('Capacity')" />
            <flux:select wire:model="status" :label="__('Status')">
                @foreach (App\Enums\EventStatus::cases() as $statusOption)
                    <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input wire:model="currency" :label="__('Currency')" maxlength="3" />
        </div>

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
            <flux:button :href="route('events.show', $event)" wire:navigate variant="ghost">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>

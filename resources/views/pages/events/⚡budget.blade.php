<?php

use App\Models\BudgetItem;
use App\Models\Event;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Budget')] class extends Component
{
    public Event $event;

    public string $category = '';

    public string $label = '';

    public string $expected_amount = '';

    public string $notes = '';

    public ?int $editingId = null;

    public function mount(Event $event): void
    {
        $this->authorize('manageFinance', $event);
        $this->event = $event;
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->category = '';
        $this->label = '';
        $this->expected_amount = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function edit(int $itemId): void
    {
        $item = $this->event->budgetItems()->findOrFail($itemId);
        $this->editingId = $item->id;
        $this->category = $item->category;
        $this->label = $item->label;
        $this->expected_amount = (string) $item->expected_amount;
        $this->notes = (string) $item->notes;
    }

    public function save(): void
    {
        $this->authorize('manageFinance', $this->event);

        $validated = $this->validate([
            'category' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
            'expected_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($this->editingId) {
            $this->event->budgetItems()->whereKey($this->editingId)->update($validated);
            Flux::toast(variant: 'success', text: __('Budget item updated.'));
        } else {
            $this->event->budgetItems()->create([
                ...$validated,
                'sort_order' => ($this->event->budgetItems()->max('sort_order') ?? 0) + 1,
            ]);
            Flux::toast(variant: 'success', text: __('Budget item added.'));
        }

        $this->resetForm();
    }

    public function delete(int $itemId): void
    {
        $this->authorize('manageFinance', $this->event);
        $this->event->budgetItems()->whereKey($itemId)->delete();
        Flux::toast(variant: 'success', text: __('Budget item deleted.'));
        $this->resetForm();
    }

    #[Computed]
    public function items()
    {
        return $this->event->budgetItems()->get();
    }

    #[Computed]
    public function total(): string
    {
        return (string) $this->event->budgetItems()->sum('expected_amount');
    }
}; ?>

<div class="space-y-6">
    <x-event-hub-nav :event="$event" />

    <flux:heading size="md">
        {{ __('Expected budget total') }}: {{ $event->currency }} {{ number_format((float) $this->total, 2) }}
    </flux:heading>

    <div class="grid gap-6 lg:grid-cols-2">
        <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="md">{{ $editingId ? __('Edit budget item') : __('Add budget item') }}</flux:heading>
            <flux:input wire:model="category" :label="__('Category')" required />
            <flux:input wire:model="label" :label="__('Label')" required />
            <flux:input wire:model="expected_amount" type="number" step="0.01" :label="__('Expected amount')" required />
            <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" />
            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">{{ $editingId ? __('Update') : __('Add') }}</flux:button>
                @if ($editingId)
                    <flux:button type="button" wire:click="resetForm" variant="ghost">{{ __('Cancel') }}</flux:button>
                @endif
            </div>
        </form>

        <div class="space-y-3">
            @forelse ($this->items as $item)
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" wire:key="budget-{{ $item->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <flux:heading size="md">{{ $item->label }}</flux:heading>
                            <flux:text class="mt-1">
                                {{ $item->category }} · {{ $event->currency }} {{ number_format((float) $item->expected_amount, 2) }}
                            </flux:text>
                            @if ($item->notes)
                                <flux:text class="mt-1">{{ $item->notes }}</flux:text>
                            @endif
                        </div>
                        <div class="flex gap-1">
                            <flux:button size="sm" variant="ghost" wire:click="edit({{ $item->id }})">{{ __('Edit') }}</flux:button>
                            <flux:button size="sm" variant="danger" wire:click="delete({{ $item->id }})" wire:confirm="{{ __('Delete?') }}">{{ __('Delete') }}</flux:button>
                        </div>
                    </div>
                </div>
            @empty
                <flux:text>{{ __('No budget items yet.') }}</flux:text>
            @endforelse
        </div>
    </div>
</div>

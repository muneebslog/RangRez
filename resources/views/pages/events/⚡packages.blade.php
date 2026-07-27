<?php

use App\Enums\PackageType;
use App\Models\Event;
use App\Models\TicketPackage;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Packages')] class extends Component
{
    public Event $event;

    public string $name = '';

    public string $type = 'early_bird';

    public string $price = '';

    public ?int $capacity = null;

    public string $sales_start_at = '';

    public string $sales_end_at = '';

    public bool $is_active = true;

    public ?int $editingId = null;

    public function mount(Event $event): void
    {
        $this->authorize('update', $event);
        $this->event = $event;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->type = PackageType::EarlyBird->value;
        $this->price = '';
        $this->capacity = $this->event->capacity;
        $this->sales_start_at = now()->format('Y-m-d\TH:i');
        $this->sales_end_at = now()->addWeek()->format('Y-m-d\TH:i');
        $this->is_active = true;
        $this->resetValidation();
    }

    public function edit(int $packageId): void
    {
        $package = $this->event->ticketPackages()->findOrFail($packageId);

        $this->editingId = $package->id;
        $this->name = $package->name;
        $this->type = $package->type->value;
        $this->price = (string) $package->price;
        $this->capacity = $package->capacity;
        $this->sales_start_at = $package->sales_start_at?->format('Y-m-d\TH:i') ?? '';
        $this->sales_end_at = $package->sales_end_at?->format('Y-m-d\TH:i') ?? '';
        $this->is_active = $package->is_active;
    }

    public function save(): void
    {
        $this->authorize('update', $this->event);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(PackageType::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'sales_start_at' => ['nullable', 'date'],
            'sales_end_at' => ['nullable', 'date', 'after:sales_start_at'],
            'is_active' => ['boolean'],
        ]);

        $payload = [
            ...$validated,
            'sales_start_at' => $validated['sales_start_at'] ?: null,
            'sales_end_at' => $validated['sales_end_at'] ?: null,
        ];

        if ($this->editingId) {
            $this->event->ticketPackages()->whereKey($this->editingId)->update($payload);
            Flux::toast(variant: 'success', text: __('Package updated.'));
        } else {
            $this->event->ticketPackages()->create($payload);
            Flux::toast(variant: 'success', text: __('Package created.'));
        }

        $this->resetForm();
    }

    public function delete(int $packageId): void
    {
        $this->authorize('update', $this->event);

        $package = $this->event->ticketPackages()->findOrFail($packageId);

        if ($package->bookings()->exists()) {
            Flux::toast(variant: 'danger', text: __('Cannot delete a package with bookings.'));

            return;
        }

        $package->delete();
        Flux::toast(variant: 'success', text: __('Package deleted.'));
        $this->resetForm();
    }

    #[Computed]
    public function packages()
    {
        return $this->event->ticketPackages()->orderBy('sales_start_at')->get();
    }
}; ?>

<div class="space-y-6">
    <x-event-hub-nav :event="$event" />

    <div class="grid gap-6 lg:grid-cols-2">
        <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="md">{{ $editingId ? __('Edit package') : __('Add package') }}</flux:heading>

            <flux:input wire:model="name" :label="__('Name')" required />
            <flux:select wire:model="type" :label="__('Type')">
                @foreach (App\Enums\PackageType::cases() as $type)
                    <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="price" type="number" step="0.01" :label="__('Price')" required />
                <flux:input wire:model="capacity" type="number" :label="__('Capacity')" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="sales_start_at" type="datetime-local" :label="__('Sales start')" />
                <flux:input wire:model="sales_end_at" type="datetime-local" :label="__('Sales end')" />
            </div>
            <flux:checkbox wire:model="is_active" :label="__('Active')" />

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">{{ $editingId ? __('Update') : __('Add') }}</flux:button>
                @if ($editingId)
                    <flux:button type="button" wire:click="resetForm" variant="ghost">{{ __('Cancel') }}</flux:button>
                @endif
            </div>
        </form>

        <div class="space-y-3">
            @foreach ($this->packages as $package)
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" wire:key="package-{{ $package->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <flux:heading size="md">{{ $package->name }}</flux:heading>
                            <flux:text class="mt-1">
                                {{ $package->type->label() }} · {{ $event->currency }} {{ number_format((float) $package->price, 2) }}
                                · {{ $package->isOnSale() ? __('On sale') : __('Not on sale') }}
                            </flux:text>
                            <flux:text class="mt-1">
                                {{ $package->sales_start_at?->format('M j, Y g:i A') ?? '—' }}
                                →
                                {{ $package->sales_end_at?->format('M j, Y g:i A') ?? '—' }}
                            </flux:text>
                        </div>
                        <div class="flex gap-1">
                            <flux:button size="sm" variant="ghost" wire:click="edit({{ $package->id }})">{{ __('Edit') }}</flux:button>
                            <flux:button size="sm" variant="danger" wire:click="delete({{ $package->id }})" wire:confirm="{{ __('Delete this package?') }}">{{ __('Delete') }}</flux:button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

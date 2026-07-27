<?php

use App\Enums\EventStatus;
use App\Enums\PackageType;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Create event')] class extends Component
{
    public string $title = '';

    public string $description = '';

    public string $venue = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public ?int $capacity = 100;

    public string $status = 'draft';

    public string $currency = 'PKR';

    public string $early_bird_price = '1500';

    public string $normal_price = '2500';

    public string $early_bird_ends_at = '';

    public function mount(): void
    {
        $this->authorize('create', Event::class);

        $this->starts_at = now()->addWeeks(2)->format('Y-m-d\TH:i');
        $this->ends_at = now()->addWeeks(2)->addHours(6)->format('Y-m-d\TH:i');
        $this->early_bird_ends_at = now()->addWeek()->format('Y-m-d\TH:i');
    }

    public function save(): void
    {
        $this->authorize('create', Event::class);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'venue' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', Rule::enum(EventStatus::class)],
            'currency' => ['required', 'string', 'size:3'],
            'early_bird_price' => ['required', 'numeric', 'min:0'],
            'normal_price' => ['required', 'numeric', 'min:0'],
            'early_bird_ends_at' => ['required', 'date'],
        ]);

        $event = Event::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'venue' => $validated['venue'] ?: null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?: null,
            'capacity' => $validated['capacity'],
            'status' => $validated['status'],
            'currency' => strtoupper($validated['currency']),
            'created_by' => Auth::id(),
        ]);

        $event->ticketPackages()->create([
            'name' => 'Early Bird',
            'type' => PackageType::EarlyBird,
            'price' => $validated['early_bird_price'],
            'capacity' => $validated['capacity'],
            'sales_start_at' => now(),
            'sales_end_at' => $validated['early_bird_ends_at'],
            'is_active' => true,
        ]);

        $event->ticketPackages()->create([
            'name' => 'Normal',
            'type' => PackageType::Normal,
            'price' => $validated['normal_price'],
            'capacity' => $validated['capacity'],
            'sales_start_at' => \Illuminate\Support\Carbon::parse($validated['early_bird_ends_at'])->addSecond(),
            'sales_end_at' => $validated['starts_at'],
            'is_active' => true,
        ]);

        $this->redirect(route('events.show', $event), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Create event') }}</flux:heading>
        <flux:text>{{ __('Set up the event and early bird / normal packages.') }}</flux:text>
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
                @foreach (App\Enums\EventStatus::cases() as $status)
                    <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input wire:model="currency" :label="__('Currency')" maxlength="3" />
        </div>

        <flux:heading size="lg" class="pt-2">{{ __('Packages') }}</flux:heading>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:input wire:model="early_bird_price" type="number" step="0.01" :label="__('Early bird price')" required />
            <flux:input wire:model="normal_price" type="number" step="0.01" :label="__('Normal price')" required />
            <flux:input wire:model="early_bird_ends_at" type="datetime-local" :label="__('Early bird ends')" required />
        </div>

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">{{ __('Create event') }}</flux:button>
            <flux:button :href="route('events.index')" wire:navigate variant="ghost">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>

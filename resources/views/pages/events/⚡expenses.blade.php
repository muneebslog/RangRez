<?php

use App\Models\Event;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] #[Title('Expenses')] class extends Component
{
    use WithFileUploads;

    public Event $event;

    public ?int $spent_by_user_id = null;

    public string $category = '';

    public string $description = '';

    public string $amount = '';

    public string $spent_at = '';

    public $receipt = null;

    public function mount(Event $event): void
    {
        $this->authorize('manageFinance', $event);
        $this->event = $event;
        $this->spent_at = now()->format('Y-m-d\TH:i');
        $this->spent_by_user_id = auth()->id();
    }

    public function save(): void
    {
        $this->authorize('manageFinance', $this->event);

        $validated = $this->validate([
            'spent_by_user_id' => ['nullable', 'exists:users,id'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'spent_at' => ['required', 'date'],
            'receipt' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,webp'],
        ]);

        $path = $this->receipt?->store('expense-receipts', 'public');

        $this->event->expenses()->create([
            'spent_by_user_id' => $validated['spent_by_user_id'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'spent_at' => $validated['spent_at'],
            'receipt_path' => $path,
        ]);

        Flux::toast(variant: 'success', text: __('Expense recorded.'));

        $this->reset(['category', 'description', 'amount', 'receipt']);
        $this->spent_at = now()->format('Y-m-d\TH:i');
        $this->spent_by_user_id = auth()->id();
    }

    public function delete(int $expenseId): void
    {
        $this->authorize('manageFinance', $this->event);
        $this->event->expenses()->whereKey($expenseId)->delete();
        Flux::toast(variant: 'success', text: __('Expense deleted.'));
    }

    #[Computed]
    public function expenses()
    {
        return $this->event->expenses()->with('spentBy')->latest('spent_at')->get();
    }

    #[Computed]
    public function staff()
    {
        return User::query()->whereIn('role', ['admin', 'finance', 'check_in'])->orderBy('name')->get();
    }

    #[Computed]
    public function total(): string
    {
        return (string) $this->event->expenses()->sum('amount');
    }
}; ?>

<div class="space-y-6">
    <x-event-hub-nav :event="$event" />

    <flux:heading size="md">
        {{ __('Total expenses') }}: {{ $event->currency }} {{ number_format((float) $this->total, 2) }}
    </flux:heading>

    <div class="grid gap-6 lg:grid-cols-2">
        <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="md">{{ __('Record expense') }}</flux:heading>

            <flux:select wire:model="spent_by_user_id" :label="__('Spent by')">
                <flux:select.option value="">{{ __('Select person') }}</flux:select.option>
                @foreach ($this->staff as $person)
                    <flux:select.option value="{{ $person->id }}">{{ $person->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="category" :label="__('Category')" required />
            <flux:input wire:model="description" :label="__('Description')" required />
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="amount" type="number" step="0.01" :label="__('Amount')" required />
                <flux:input wire:model="spent_at" type="datetime-local" :label="__('Spent at')" required />
            </div>
            <flux:input wire:model="receipt" type="file" :label="__('Receipt / proof')" />

            <flux:button type="submit" variant="primary">{{ __('Save expense') }}</flux:button>
        </form>

        <div class="space-y-3">
            @forelse ($this->expenses as $expense)
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" wire:key="expense-{{ $expense->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <flux:heading size="md">{{ $expense->description }}</flux:heading>
                            <flux:text class="mt-1">
                                {{ $expense->category }} · {{ $event->currency }} {{ number_format((float) $expense->amount, 2) }}
                                · {{ $expense->spent_at->format('M j, Y') }}
                                @if ($expense->spentBy)
                                    · {{ $expense->spentBy->name }}
                                @endif
                            </flux:text>
                            @if ($expense->receiptUrl())
                                <a href="{{ $expense->receiptUrl() }}" target="_blank" class="mt-2 inline-block text-sm underline">{{ __('View receipt') }}</a>
                            @endif
                        </div>
                        <flux:button size="sm" variant="danger" wire:click="delete({{ $expense->id }})" wire:confirm="{{ __('Delete?') }}">{{ __('Delete') }}</flux:button>
                    </div>
                </div>
            @empty
                <flux:text>{{ __('No expenses recorded.') }}</flux:text>
            @endforelse
        </div>
    </div>
</div>

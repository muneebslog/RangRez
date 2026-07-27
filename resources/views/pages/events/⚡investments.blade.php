<?php

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] #[Title('Investments')] class extends Component
{
    use WithFileUploads;

    public Event $event;

    public ?int $user_id = null;

    public string $amount = '';

    public string $share_percent = '';

    public string $notes = '';

    public string $invested_at = '';

    public $proof = null;

    public function mount(Event $event): void
    {
        abort_unless(
            Auth::user()->canManageFinance() || (Auth::user()->isInvestor() && $event->investments()->where('user_id', Auth::id())->exists()),
            403
        );

        $this->authorize('view', $event);
        $this->event = $event;
        $this->invested_at = now()->format('Y-m-d\TH:i');
    }

    public function save(): void
    {
        $this->authorize('manageFinance', $this->event);

        $validated = $this->validate([
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'share_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'invested_at' => ['required', 'date'],
            'proof' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,webp'],
        ]);

        $path = $this->proof?->store('investment-proofs', 'public');

        $this->event->investments()->create([
            'user_id' => $validated['user_id'],
            'amount' => $validated['amount'],
            'share_percent' => $validated['share_percent'] ?: null,
            'notes' => $validated['notes'] ?: null,
            'invested_at' => $validated['invested_at'],
            'proof_path' => $path,
        ]);

        Flux::toast(variant: 'success', text: __('Investment recorded.'));

        $this->reset(['user_id', 'amount', 'share_percent', 'notes', 'proof']);
        $this->invested_at = now()->format('Y-m-d\TH:i');
    }

    public function delete(int $investmentId): void
    {
        $this->authorize('manageFinance', $this->event);
        $this->event->investments()->whereKey($investmentId)->delete();
        Flux::toast(variant: 'success', text: __('Investment deleted.'));
    }

    #[Computed]
    public function investments()
    {
        $query = $this->event->investments()->with('user')->latest('invested_at');

        if (Auth::user()->isInvestor() && ! Auth::user()->canManageFinance()) {
            $query->where('user_id', Auth::id());
        }

        return $query->get();
    }

    #[Computed]
    public function investors()
    {
        return User::query()
            ->where('role', UserRole::Investor)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function total(): string
    {
        return (string) $this->investments->sum('amount');
    }
}; ?>

<div class="space-y-6">
    <x-event-hub-nav :event="$event" />

    <flux:heading size="md">
        {{ __('Total invested') }}: {{ $event->currency }} {{ number_format((float) $this->total, 2) }}
    </flux:heading>

    <div class="grid gap-6 lg:grid-cols-2">
        @can('manageFinance', $event)
            <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:heading size="md">{{ __('Record investment') }}</flux:heading>

                <flux:select wire:model="user_id" :label="__('Investor')" required>
                    <flux:select.option value="">{{ __('Select investor') }}</flux:select.option>
                    @foreach ($this->investors as $investor)
                        <flux:select.option value="{{ $investor->id }}">{{ $investor->name }} ({{ $investor->email }})</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="amount" type="number" step="0.01" :label="__('Amount')" required />
                    <flux:input wire:model="share_percent" type="number" step="0.01" :label="__('Share %')" />
                </div>

                <flux:input wire:model="invested_at" type="datetime-local" :label="__('Invested at')" required />
                <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" />
                <flux:input wire:model="proof" type="file" :label="__('Proof')" />

                <flux:button type="submit" variant="primary">{{ __('Save investment') }}</flux:button>
            </form>
        @endcan

        <div class="space-y-3">
            @forelse ($this->investments as $investment)
                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" wire:key="investment-{{ $investment->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <flux:heading size="md">{{ $investment->user->name }}</flux:heading>
                            <flux:text class="mt-1">
                                {{ $event->currency }} {{ number_format((float) $investment->amount, 2) }}
                                @if ($investment->share_percent)
                                    · {{ number_format((float) $investment->share_percent, 2) }}%
                                @endif
                                · {{ $investment->invested_at->format('M j, Y') }}
                            </flux:text>
                            @if ($investment->notes)
                                <flux:text class="mt-1">{{ $investment->notes }}</flux:text>
                            @endif
                            @if ($investment->proofUrl())
                                <a href="{{ $investment->proofUrl() }}" target="_blank" class="mt-2 inline-block text-sm underline">{{ __('View proof') }}</a>
                            @endif
                        </div>
                        @can('manageFinance', $event)
                            <flux:button size="sm" variant="danger" wire:click="delete({{ $investment->id }})" wire:confirm="{{ __('Delete?') }}">{{ __('Delete') }}</flux:button>
                        @endcan
                    </div>
                </div>
            @empty
                <flux:text>{{ __('No investments recorded.') }}</flux:text>
            @endforelse
        </div>
    </div>
</div>

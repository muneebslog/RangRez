<?php

use App\Models\Investment;
use App\Services\EventFinancialSummary;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('My investments')] class extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::user()->isInvestor() || Auth::user()->canManageFinance(), 403);
    }

    #[Computed]
    public function investments()
    {
        $query = Investment::query()->with('event')->latest('invested_at');

        if (Auth::user()->isInvestor() && ! Auth::user()->canManageFinance()) {
            $query->where('user_id', Auth::id());
        }

        return $query->get();
    }

    #[Computed]
    public function summaries(): array
    {
        $service = app(EventFinancialSummary::class);
        $result = [];

        foreach ($this->investments->pluck('event')->unique('id') as $event) {
            $result[$event->id] = $service->for($event);
        }

        return $result;
    }
}; ?>

<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('My investments') }}</flux:heading>
        <flux:text>{{ __('Your investments and shared event financial summaries.') }}</flux:text>
    </div>

    <div class="space-y-4">
        @forelse ($this->investments as $investment)
            @php($summary = $this->summaries[$investment->event_id] ?? null)
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700" wire:key="my-investment-{{ $investment->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <flux:heading size="md">
                            <a href="{{ route('events.show', $investment->event) }}" wire:navigate class="hover:underline">
                                {{ $investment->event->title }}
                            </a>
                        </flux:heading>
                        <flux:text class="mt-1">
                            {{ __('Invested') }}:
                            {{ $investment->event->currency }} {{ number_format((float) $investment->amount, 2) }}
                            @if ($investment->share_percent)
                                · {{ number_format((float) $investment->share_percent, 2) }}%
                            @endif
                            · {{ $investment->invested_at->format('M j, Y') }}
                        </flux:text>
                        @if ($summary)
                            <flux:text class="mt-2">
                                {{ __('Revenue') }}: {{ number_format((float) $summary['revenue'], 2) }}
                                · {{ __('Expenses') }}: {{ number_format((float) $summary['expenses'], 2) }}
                                · {{ __('P/L') }}: {{ number_format((float) $summary['profit_loss'], 2) }}
                            </flux:text>
                        @endif
                    </div>
                    <flux:button :href="route('events.show', $investment->event)" wire:navigate size="sm" variant="ghost">
                        {{ __('View event') }}
                    </flux:button>
                </div>
            </div>
        @empty
            <flux:text>{{ __('No investments yet.') }}</flux:text>
        @endforelse
    </div>
</div>

<?php

use App\Models\Booking;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.public')] #[Title('Booking status')] class extends Component
{
    use WithFileUploads;

    public Booking $booking;

    public $payment_proof = null;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['event', 'ticketPackage']);
    }

    public function uploadProof(): void
    {
        $this->validate([
            'payment_proof' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,webp'],
        ]);

        $path = $this->payment_proof->store('payment-proofs', 'public');

        $this->booking->update(['payment_proof_path' => $path]);
        $this->payment_proof = null;

        Flux::toast(variant: 'success', text: __('Payment proof uploaded.'));
    }
}; ?>

<div class="mx-auto max-w-lg space-y-6">
    <div>
        <p class="text-sm uppercase tracking-[0.25em] text-rr-accent">{{ __('Ticket') }}</p>
        <h1 class="mt-2 font-serif text-3xl tracking-tight text-rr-fg sm:text-4xl">{{ __('Booking confirmed') }}</h1>
        <p class="mt-2 text-rr-muted">{{ __('Save your ticket code for check-in.') }}</p>
    </div>

    <div class="rr-surface space-y-4 rounded-xl p-6">
        <div>
            <p class="text-sm text-rr-muted">{{ __('Event') }}</p>
            <div class="text-lg font-semibold text-rr-fg">{{ $booking->event->title }}</div>
        </div>
        <div>
            <p class="text-sm text-rr-muted">{{ __('Ticket code') }}</p>
            <div class="font-mono text-2xl font-semibold tracking-wider text-rr-accent">{{ $booking->ticket_code }}</div>
        </div>
        <div>
            <p class="text-sm text-rr-muted">{{ __('Guest') }}</p>
            <div class="font-medium text-rr-fg">{{ $booking->name }} · {{ $booking->email }}</div>
        </div>
        <div>
            <p class="text-sm text-rr-muted">{{ __('Package') }}</p>
            <div class="font-medium text-rr-fg">
                {{ $booking->ticketPackage->name }} ·
                {{ $booking->event->currency }} {{ number_format((float) $booking->price, 2) }}
            </div>
        </div>
        <div>
            <p class="text-sm text-rr-muted">{{ __('Payment status') }}</p>
            <div class="font-medium text-rr-fg">{{ $booking->payment_status->label() }}</div>
        </div>
        @if ($booking->isCheckedIn())
            <div>
                <p class="text-sm text-rr-muted">{{ __('Checked in') }}</p>
                <div class="font-medium text-rr-fg">{{ $booking->checked_in_at->format('M j, Y g:i A') }}</div>
            </div>
        @endif
    </div>

    @if ($booking->payment_status === App\Enums\PaymentStatus::Pending)
        <form wire:submit="uploadProof" class="rr-surface space-y-3 rounded-xl p-4">
            <h2 class="text-lg font-semibold text-rr-fg">{{ __('Upload payment proof') }}</h2>
            @if ($booking->paymentProofUrl())
                <a href="{{ $booking->paymentProofUrl() }}" target="_blank" class="text-sm text-rr-accent underline">{{ __('Current proof') }}</a>
            @endif
            <flux:input wire:model="payment_proof" type="file" :label="__('Proof file')" />
            <flux:button type="submit" variant="primary">{{ __('Upload') }}</flux:button>
        </form>
    @endif

    <flux:button :href="route('public.events.show', $booking->event)" wire:navigate variant="ghost">
        {{ __('Back to event') }}
    </flux:button>
</div>

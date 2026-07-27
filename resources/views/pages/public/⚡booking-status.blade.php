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
        <flux:heading size="xl">{{ __('Booking confirmed') }}</flux:heading>
        <flux:text class="mt-2">{{ __('Save your ticket code for check-in.') }}</flux:text>
    </div>

    <div class="space-y-3 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <div>
            <flux:text>{{ __('Event') }}</flux:text>
            <flux:heading size="md">{{ $booking->event->title }}</flux:heading>
        </div>
        <div>
            <flux:text>{{ __('Ticket code') }}</flux:text>
            <flux:heading size="lg" class="font-mono tracking-wider">{{ $booking->ticket_code }}</flux:heading>
        </div>
        <div>
            <flux:text>{{ __('Guest') }}</flux:text>
            <div class="font-medium">{{ $booking->name }} · {{ $booking->email }}</div>
        </div>
        <div>
            <flux:text>{{ __('Package') }}</flux:text>
            <div class="font-medium">
                {{ $booking->ticketPackage->name }} ·
                {{ $booking->event->currency }} {{ number_format((float) $booking->price, 2) }}
            </div>
        </div>
        <div>
            <flux:text>{{ __('Payment status') }}</flux:text>
            <div class="font-medium">{{ $booking->payment_status->label() }}</div>
        </div>
        @if ($booking->isCheckedIn())
            <div>
                <flux:text>{{ __('Checked in') }}</flux:text>
                <div class="font-medium">{{ $booking->checked_in_at->format('M j, Y g:i A') }}</div>
            </div>
        @endif
    </div>

    @if ($booking->payment_status === App\Enums\PaymentStatus::Pending)
        <form wire:submit="uploadProof" class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="md">{{ __('Upload payment proof') }}</flux:heading>
            @if ($booking->paymentProofUrl())
                <a href="{{ $booking->paymentProofUrl() }}" target="_blank" class="text-sm underline">{{ __('Current proof') }}</a>
            @endif
            <flux:input wire:model="payment_proof" type="file" :label="__('Proof file')" />
            <flux:button type="submit" variant="primary">{{ __('Upload') }}</flux:button>
        </form>
    @endif

    <flux:button :href="route('public.events.show', $booking->event)" wire:navigate variant="ghost">
        {{ __('Back to event') }}
    </flux:button>
</div>

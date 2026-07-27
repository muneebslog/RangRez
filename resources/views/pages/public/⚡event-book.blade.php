<?php

use App\Enums\PaymentStatus;
use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Models\Event;
use App\Support\TicketCodeGenerator;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.public')] #[Title('Book event')] class extends Component
{
    use WithFileUploads;

    public Event $event;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public $payment_proof = null;

    public function mount(Event $event): void
    {
        abort_unless($event->isBookable(), 404);

        $this->event = $event;
    }

    #[Computed]
    public function package()
    {
        return $this->event->currentPackage();
    }

    public function book(): void
    {
        abort_unless($this->event->isBookable(), 404);

        $package = $this->event->currentPackage();

        if (! $package) {
            $this->addError('name', __('Ticket sales are not open right now.'));

            return;
        }

        if ($this->event->isSoldOut() || $package->isSoldOut()) {
            $this->addError('name', __('This event is sold out.'));

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'payment_proof' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,webp'],
        ]);

        $proofPath = $this->payment_proof?->store('payment-proofs', 'public');

        $booking = Booking::create([
            'event_id' => $this->event->id,
            'ticket_package_id' => $package->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'ticket_code' => app(TicketCodeGenerator::class)->generate(),
            'payment_status' => PaymentStatus::Pending,
            'price' => $package->price,
            'amount_paid' => 0,
            'payment_proof_path' => $proofPath,
        ]);

        Mail::to($booking->email)->send(new BookingConfirmed($booking->load(['event', 'ticketPackage'])));

        $this->redirect(route('bookings.status', $booking), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-lg space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Register for :event', ['event' => $event->title]) }}</flux:heading>
        @if ($this->package)
            <flux:text class="mt-2">
                {{ $this->package->name }} · {{ $event->currency }} {{ number_format((float) $this->package->price, 2) }}
            </flux:text>
        @endif
    </div>

    @if (! $this->package)
        <flux:text>{{ __('Ticket sales are not open right now.') }}</flux:text>
    @else
        <form wire:submit="book" class="space-y-4">
            <flux:input wire:model="name" :label="__('Full name')" required autofocus />
            <flux:input wire:model="email" type="email" :label="__('Email')" required />
            <flux:input wire:model="phone" :label="__('Phone')" />
            <flux:input wire:model="payment_proof" type="file" :label="__('Payment proof (optional)')" />
            <flux:text>{{ __('You can upload proof now or later. Staff will mark your payment once verified.') }}</flux:text>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ __('Complete registration') }}</flux:button>
                <flux:button :href="route('public.events.show', $event)" wire:navigate variant="ghost">{{ __('Back') }}</flux:button>
            </div>
        </form>
    @endif
</div>

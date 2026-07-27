<x-mail::message>
# {{ __('Booking Confirmed') }}

{{ __('Hi :name,', ['name' => $booking->name]) }}

{{ __('Your booking for **:event** is confirmed.', ['event' => $event->title]) }}

**{{ __('Ticket code') }}:** {{ $booking->ticket_code }}

**{{ __('Package') }}:** {{ $booking->ticketPackage->name }}

**{{ __('Amount') }}:** {{ $event->currency }} {{ number_format((float) $booking->price, 2) }}

**{{ __('Payment status') }}:** {{ $booking->payment_status->label() }}

<x-mail::button :url="$statusUrl">
{{ __('View booking status') }}
</x-mail::button>

{{ __('Thanks') }},<br>
{{ config('app.name') }}
</x-mail::message>

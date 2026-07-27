<?php

namespace App\Support;

use App\Models\Booking;
use Illuminate\Support\Str;

class TicketCodeGenerator
{
    public function generate(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Booking::withTrashed()->where('ticket_code', $code)->exists());

        return $code;
    }
}

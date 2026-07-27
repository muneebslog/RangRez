<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingExportController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Event $event): StreamedResponse
    {
        $this->authorize('manageBookings', $event);

        $filename = $event->slug.'-bookings.csv';

        return response()->streamDownload(function () use ($event): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ticket_code',
                'name',
                'email',
                'phone',
                'package',
                'price',
                'payment_status',
                'amount_paid',
                'checked_in_at',
                'created_at',
            ]);

            $event->bookings()
                ->with('ticketPackage')
                ->orderBy('id')
                ->chunk(200, function ($bookings) use ($handle): void {
                    foreach ($bookings as $booking) {
                        fputcsv($handle, [
                            $booking->ticket_code,
                            $booking->name,
                            $booking->email,
                            $booking->phone,
                            $booking->ticketPackage->name,
                            $booking->price,
                            $booking->payment_status->value,
                            $booking->amount_paid,
                            $booking->checked_in_at?->toDateTimeString(),
                            $booking->created_at?->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}

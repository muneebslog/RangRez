<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $event_id
 * @property int $ticket_package_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $ticket_code
 * @property PaymentStatus $payment_status
 * @property string $price
 * @property string $amount_paid
 * @property string|null $payment_proof_path
 * @property Carbon|null $checked_in_at
 * @property string|null $notes
 * @property int|null $created_by
 */
#[Fillable([
    'event_id',
    'ticket_package_id',
    'name',
    'email',
    'phone',
    'ticket_code',
    'payment_status',
    'price',
    'amount_paid',
    'payment_proof_path',
    'checked_in_at',
    'notes',
    'created_by',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'payment_status' => PaymentStatus::class,
            'price' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'checked_in_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketPackage(): BelongsTo
    {
        return $this->belongsTo(TicketPackage::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    public function canCheckIn(): bool
    {
        return ! $this->isCheckedIn() && $this->payment_status->allowsCheckIn();
    }

    public function markPaid(?string $amount = null): void
    {
        $this->update([
            'payment_status' => PaymentStatus::Paid,
            'amount_paid' => $amount ?? $this->price,
        ]);
    }

    public function checkIn(): void
    {
        $this->update(['checked_in_at' => now()]);
    }

    public function undoCheckIn(): void
    {
        $this->update(['checked_in_at' => null]);
    }

    public function paymentProofUrl(): ?string
    {
        if (! $this->payment_proof_path) {
            return null;
        }

        return Storage::disk('public')->url($this->payment_proof_path);
    }

    public function getRouteKeyName(): string
    {
        return 'ticket_code';
    }
}

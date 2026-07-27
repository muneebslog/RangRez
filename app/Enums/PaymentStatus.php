<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Waived = 'waived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Waived => 'Waived',
        };
    }

    public function countsTowardCapacity(): bool
    {
        return true;
    }

    public function allowsCheckIn(): bool
    {
        return in_array($this, [self::Paid, self::Waived], true);
    }
}

<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Finance = 'finance';
    case CheckIn = 'check_in';
    case Investor = 'investor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Finance => 'Finance',
            self::CheckIn => 'Check-in',
            self::Investor => 'Investor',
        };
    }

    public function canManageEvents(): bool
    {
        return $this === self::Admin;
    }

    public function canManageFinance(): bool
    {
        return in_array($this, [self::Admin, self::Finance], true);
    }

    public function canCheckIn(): bool
    {
        return in_array($this, [self::Admin, self::CheckIn], true);
    }

    public function canViewReports(): bool
    {
        return in_array($this, [self::Admin, self::Finance, self::Investor], true);
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::Admin, self::Finance, self::CheckIn], true);
    }
}

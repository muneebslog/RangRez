<?php

namespace App\Enums;

enum PackageType: string
{
    case EarlyBird = 'early_bird';
    case Normal = 'normal';

    public function label(): string
    {
        return match ($this) {
            self::EarlyBird => 'Early Bird',
            self::Normal => 'Normal',
        };
    }
}

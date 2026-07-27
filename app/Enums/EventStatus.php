<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Live = 'live';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Live => 'Live',
            self::Closed => 'Closed',
        };
    }

    public function isBookable(): bool
    {
        return in_array($this, [self::Published, self::Live], true);
    }
}

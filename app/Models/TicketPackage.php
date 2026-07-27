<?php

namespace App\Models;

use App\Enums\PackageType;
use Database\Factories\TicketPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property PackageType $type
 * @property string $price
 * @property int|null $capacity
 * @property Carbon|null $sales_start_at
 * @property Carbon|null $sales_end_at
 * @property bool $is_active
 */
#[Fillable([
    'event_id',
    'name',
    'type',
    'price',
    'capacity',
    'sales_start_at',
    'sales_end_at',
    'is_active',
])]
class TicketPackage extends Model
{
    /** @use HasFactory<TicketPackageFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => PackageType::class,
            'price' => 'decimal:2',
            'capacity' => 'integer',
            'sales_start_at' => 'datetime',
            'sales_end_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isOnSale(?Carbon $at = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $at ??= now();

        if ($this->sales_start_at && $at->lt($this->sales_start_at)) {
            return false;
        }

        if ($this->sales_end_at && $at->gt($this->sales_end_at)) {
            return false;
        }

        return true;
    }

    public function bookedCount(): int
    {
        return $this->bookings()->count();
    }

    public function remainingCapacity(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        return max(0, $this->capacity - $this->bookedCount());
    }

    public function isSoldOut(): bool
    {
        $remaining = $this->remainingCapacity();

        return $remaining !== null && $remaining <= 0;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

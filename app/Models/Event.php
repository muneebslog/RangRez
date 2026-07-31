<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\PaymentStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $venue
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 * @property int|null $capacity
 * @property EventStatus $status
 * @property string $currency
 * @property int|null $created_by
 */
#[Fillable([
    'title',
    'slug',
    'description',
    'venue',
    'starts_at',
    'ends_at',
    'capacity',
    'status',
    'currency',
    'created_by',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => EventStatus::class,
            'capacity' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event): void {
            if (blank($event->slug)) {
                $event->slug = static::uniqueSlugFromTitle($event->title);
            }
        });
    }

    public static function uniqueSlugFromTitle(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ticketPackages(): HasMany
    {
        return $this->hasMany(TicketPackage::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(BudgetItem::class)->orderBy('sort_order');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeBookable(Builder $query): Builder
    {
        return $query->whereIn('status', [EventStatus::Published, EventStatus::Live]);
    }

    public function isBookable(): bool
    {
        return $this->status->isBookable();
    }

    public function currentPackage(?Carbon $at = null): ?TicketPackage
    {
        $at ??= now();

        return $this->ticketPackages()
            ->where('is_active', true)
            ->where(function (Builder $query) use ($at): void {
                $query->whereNull('sales_start_at')->orWhere('sales_start_at', '<=', $at);
            })
            ->where(function (Builder $query) use ($at): void {
                $query->whereNull('sales_end_at')->orWhere('sales_end_at', '>=', $at);
            })
            ->orderByRaw("case when type = 'early_bird' then 0 else 1 end")
            ->first();
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

    public function revenue(): string
    {
        return (string) $this->bookings()
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('amount_paid');
    }

    public function totalExpenses(): string
    {
        return (string) $this->expenses()->sum('amount');
    }

    public function totalBudget(): string
    {
        return (string) $this->budgetItems()->sum('expected_amount');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

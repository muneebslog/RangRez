<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $event_id
 * @property int|null $spent_by_user_id
 * @property bool $paid_by_company
 * @property string $category
 * @property string $description
 * @property string $amount
 * @property string|null $receipt_path
 * @property Carbon $spent_at
 * @property Carbon|null $repaid_at
 */
#[Fillable([
    'event_id',
    'spent_by_user_id',
    'paid_by_company',
    'category',
    'description',
    'amount',
    'receipt_path',
    'spent_at',
    'repaid_at',
])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_by_company' => 'boolean',
            'spent_at' => 'datetime',
            'repaid_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function spentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'spent_by_user_id');
    }

    public function needsRepayment(): bool
    {
        return ! $this->paid_by_company && $this->repaid_at === null;
    }

    public function isRepaid(): bool
    {
        return ! $this->paid_by_company && $this->repaid_at !== null;
    }

    public function receiptUrl(): ?string
    {
        if (! $this->receipt_path) {
            return null;
        }

        return Storage::disk('public')->url($this->receipt_path);
    }
}

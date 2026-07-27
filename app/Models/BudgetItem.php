<?php

namespace App\Models;

use Database\Factories\BudgetItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property string $category
 * @property string $label
 * @property string $expected_amount
 * @property string|null $notes
 * @property int $sort_order
 */
#[Fillable([
    'event_id',
    'category',
    'label',
    'expected_amount',
    'notes',
    'sort_order',
])]
class BudgetItem extends Model
{
    /** @use HasFactory<BudgetItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

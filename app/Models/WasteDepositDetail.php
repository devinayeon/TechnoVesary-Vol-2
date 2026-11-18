<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'waste_deposit_id',
    'waste_category_id',
    'points_per_kg',
    'weight',
    'subtotal_points',
])]
class WasteDepositDetail extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'points_per_kg' => 'integer',
            'weight' => 'decimal:2',
            'subtotal_points' => 'integer',
        ];
    }

    public function wasteDeposit(): BelongsTo
    {
        return $this->belongsTo(WasteDeposit::class);
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }

    /**
     * Apakah kategori ini sudah ditimbang oleh driver.
     */
    public function isWeighed(): bool
    {
        return $this->weight !== null;
    }
}

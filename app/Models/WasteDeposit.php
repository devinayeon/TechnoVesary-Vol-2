<?php

namespace App\Models;

use App\Enums\PickupStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_id',
    'driver_id',
    'status',
    'address',
    'notes',
    'latitude',
    'longitude',
    'photo',
    'pickup_proof_photo',
    'pickup_at',
])]
class WasteDeposit extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => PickupStatus::class,
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'pickup_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(WasteDepositDetail::class);
    }
}

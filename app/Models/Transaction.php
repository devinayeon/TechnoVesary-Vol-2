<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_id',
    'driver_id',
    'total_amount',
    'points_used',
    'points_discount_amount',
    'payable_amount',
    'payment_method',
    'status',
    'snap_url',
    'midtrans_order_id',
    'delivery_address',
    'latitude',
    'longitude',
    'delivery_proof_photo',
    'settled_at',
    'delivered_at',
    'canceled_at',
])]
class Transaction extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'points_used' => 'integer',
            'points_discount_amount' => 'integer',
            'payable_amount' => 'integer',
            'payment_method' => PaymentMethod::class,
            'status' => OrderStatus::class,
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'settled_at' => 'datetime',
            'delivered_at' => 'datetime',
            'canceled_at' => 'datetime',
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
        return $this->hasMany(TransactionDetail::class);
    }
}

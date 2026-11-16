<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_id',
    'subscription_id',
    'total_amount',
    'points_used',
    'points_discount_amount',
    'payable_amount',
    'payment_method',
    'snap_url',
    'status',
    'settled_at',
    'canceled_at',
])]
class SubscriptionTransaction extends Model
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
            'status' => TransactionStatus::class,
            'settled_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}

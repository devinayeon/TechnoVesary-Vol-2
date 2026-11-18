<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property int $subscription_id
 * @property string $package_key
 * @property int $duration_months
 * @property int $total_amount
 * @property int $points_used
 * @property int $points_discount_amount
 * @property int $payable_amount
 * @property PaymentMethod|null $payment_method
 * @property string|null $snap_url
 * @property string|null $snap_token
 * @property string|null $midtrans_order_id
 * @property TransactionStatus $status
 * @property Carbon|null $settled_at
 * @property Carbon|null $canceled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read Subscription $subscription
 */
#[Fillable([
    'customer_id',
    'subscription_id',
    'package_key',
    'duration_months',
    'total_amount',
    'points_used',
    'points_discount_amount',
    'payable_amount',
    'payment_method',
    'snap_url',
    'snap_token',
    'midtrans_order_id',
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
            'duration_months' => 'integer',
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

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}

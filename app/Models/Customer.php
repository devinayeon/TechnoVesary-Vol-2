<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'points', 'is_member', 'membership_expires_at'])]
class Customer extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'is_member' => 'boolean',
            'membership_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionTransactions(): HasMany
    {
        return $this->hasMany(SubscriptionTransaction::class);
    }

    public function wasteDeposits(): HasMany
    {
        return $this->hasMany(WasteDeposit::class);
    }

    public function pointHistories(): HasMany
    {
        return $this->hasMany(PointHistory::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}

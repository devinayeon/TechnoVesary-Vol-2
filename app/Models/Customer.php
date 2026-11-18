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

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['poin', 'expired_member_date'];

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

    /**
     * Apakah customer memiliki langganan ReValue+ yang masih aktif.
     */
    public function isActiveMember(): bool
    {
        if (! $this->is_member) {
            return false;
        }

        return $this->membership_expires_at === null
            || $this->membership_expires_at->isFuture();
    }

    /**
     * Accessors untuk kompatibilitas skema DBML/SQL (yeon-ui-dev/revalue.sql)
     */
    public function getPoinAttribute(): int
    {
        return (int) $this->points;
    }

    public function getExpiredMemberDateAttribute(): mixed
    {
        return $this->membership_expires_at;
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

<?php

namespace App\Models;

use App\Enums\PickupStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = [
        'photo_url',
        'pickup_proof_photo_url',
        'total_points',
        'total_weight',
    ];

    protected function casts(): array
    {
        return [
            'status' => PickupStatus::class,
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'pickup_at' => 'datetime',
        ];
    }

    /**
     * URL publik foto sampah dari customer.
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->resolvePublicUrl($this->photo)
        );
    }

    /**
     * URL publik foto bukti penjemputan dari driver.
     */
    protected function pickupProofPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->resolvePublicUrl($this->pickup_proof_photo)
        );
    }

    /**
     * Total poin yang diterbitkan (0 sebelum penimbangan selesai).
     */
    protected function totalPoints(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->details->sum(fn ($detail) => (int) ($detail->subtotal_points ?? 0))
        );
    }

    /**
     * Total berat sampah yang ditimbang (0 sebelum penimbangan selesai).
     */
    protected function totalWeight(): Attribute
    {
        return Attribute::make(
            get: fn () => round((float) $this->details->sum(fn ($detail) => (float) ($detail->weight ?? 0)), 2)
        );
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

    /**
     * Batasi query pada penjemputan milik satu customer.
     */
    public function scopeForCustomer(Builder $query, Customer|int $customer): Builder
    {
        return $query->where(
            'customer_id',
            $customer instanceof Customer ? $customer->id : $customer
        );
    }

    /**
     * Konversi path storage menjadi URL publik.
     */
    protected function resolvePublicUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

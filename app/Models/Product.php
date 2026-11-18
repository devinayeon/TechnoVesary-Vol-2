<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'admin_id',
    'name',
    'description',
    'price_rupiah',
    'price_points',
    'photo',
    'stock',
])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return [
            'price_rupiah' => 'integer',
            'price_points' => 'integer',
            'stock' => 'integer',
        ];
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->photo) {
                    return null;
                }
                if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://')) {
                    return $this->photo;
                }

                return Storage::disk('public')->url($this->photo);
            }
        );
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}

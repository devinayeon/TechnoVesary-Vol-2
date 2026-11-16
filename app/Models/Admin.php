<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id'])]
class Admin extends Model
{
    use HasFactory;

    /**
     * Relasi ke entitas akun User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Produk upcycle yang dikelola admin
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Artikel edukasi yang dipublikasikan admin
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}

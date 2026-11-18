<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'admin_id',
    'category_id',
    'title',
    'slug',
    'photo',
    'content',
])]
class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['photo_url'];

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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }
}

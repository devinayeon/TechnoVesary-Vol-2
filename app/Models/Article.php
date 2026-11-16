<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }
}

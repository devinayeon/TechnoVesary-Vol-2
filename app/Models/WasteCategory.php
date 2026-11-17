<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description', 'points_per_kg'])]
class WasteCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'points_per_kg' => 'integer',
        ];
    }

    public function wasteDepositDetails(): HasMany
    {
        return $this->hasMany(WasteDepositDetail::class);
    }
}

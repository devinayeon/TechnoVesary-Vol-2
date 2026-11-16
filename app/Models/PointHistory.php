<?php

namespace App\Models;

use App\Enums\PointType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_id', 'type', 'amount', 'source'])]
class PointHistory extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => PointType::class,
            'amount' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

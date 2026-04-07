<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountRedemption extends Model
{
    protected $fillable = [
        'discount_code_id',
        'user_id',
        'context',
        'discount_applied',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
            'discount_applied' => 'decimal:2',
        ];
    }

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

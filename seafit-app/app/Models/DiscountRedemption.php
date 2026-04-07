<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Historial de usos de codigos de descuento.
 */
class DiscountRedemption extends Model
{
    /**
     * Campos permitidos al crear registros de uso.
     */
    protected $fillable = [
        'discount_code_id',
        'user_id',
        'context',
        'discount_applied',
        'applied_at',
    ];

    /**
     * Conversion automatica de tipos.
     */
    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
            'discount_applied' => 'decimal:2',
        ];
    }

    /**
     * Codigo de descuento asociado.
     */
    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    /**
     * Usuario que uso el codigo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

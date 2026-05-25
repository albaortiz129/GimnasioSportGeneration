<?php

/**
 * Guarda qué usuario aplicó cada código y en qué contexto.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Historial de usos de códigos de descuento.
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
     * Conversión automática de tipos.
     */
    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime', // Fecha y hora en que se usó el código.
            'discount_applied' => 'decimal:2', // Descuento aplicado.
        ];
    }

    /**
     * Código de descuento asociado.
     */
    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class); // Pertenece a un código de descuento.
    }

    /**
     * Usuario que usó el código.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // Pertenece a un usuario.
    }
}

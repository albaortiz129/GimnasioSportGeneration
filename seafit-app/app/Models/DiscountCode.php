<?php

/**
 * Define reglas de validez, uso y cálculo de descuentos.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de códigos de descuento administrables desde el panel de administrador.
 */
class DiscountCode extends Model
{
    /**
     * Campos permitidos al crear o editar.
     */
    protected $fillable = [
        'code',
        'type',
        'value',
        'is_active',
        'starts_at',
        'ends_at',
        'max_uses',
        'used_count',
        'one_use_per_user',
        'stripe_coupon_id',
        'notes',
        'created_by',
    ];

    /**
     * Conversión automática de tipos.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'one_use_per_user' => 'boolean',
            'value' => 'decimal:2',
        ];
    }

    /**
     * Fuerza el código en mayúsculas antes de guardar.
     */
    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->code = strtoupper(trim($model->code));
        });
    }

    /**
     * Relación con los usos del código.
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    /**
     * Buscar por código.
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', strtoupper(trim($code)));
    }

    /**
     * Comprueba si el código está activo ahora mismo.
     */
    public function isActiveNow(): bool
    {
        $now = now();

        // Debe estar activo en panel.
        if (!$this->is_active) {
            return false;
        }

        // Si aún no empezó, no aplica.
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        // Si ya venció, no aplica.
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        // Si alcanzó límite de usos, no aplica.
        if (!is_null($this->max_uses) && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Comprueba si un usuario puede usar este código.
     */
    public function canBeUsedBy(User $user, string $context = 'registro'): bool
    {
        if (!$this->isActiveNow()) { // Verifica si el código está activo.
            return false;
        }

        if (!$this->one_use_per_user) { // Verifica si es de un solo uso.
            return true;
        }

        return !$this->redemptions()
            ->where('user_id', $user->id) // Busca el usuario.
            ->where('context', $context) // Busca el contexto (registro, renovación, etc).
            ->exists(); // Verifica si el usuario ya ha usado el código en este contexto.
    }

    /**
     * Marca el código como usado y guarda el historial.
     */
    public function markUsed(User $user, string $context = 'registro', ?float $amount = null): void
    {
        $this->increment('used_count'); // Incrementa el contador de usos.

        $this->redemptions()->create([ // Crea un registro.
            'user_id' => $user->id,
            'context' => $context,
            'discount_applied' => $amount,
            'applied_at' => now(),
        ]);
    }

    /**
     * Calcula cuanto descuento se aplica sobre un importe base.
     */
    public function calculateDiscountAmount(float $baseAmount): float
    {
        $baseAmount = max($baseAmount, 0); // Asegura que el importe base no sea negativo.

        $discount = $this->type === 'percent' // Si es porcentaje, calcula el descuento.
            ? ($baseAmount * ((float) $this->value / 100))
            : (float) $this->value; // Si es fijo, usa el valor fijo.

        return round(min($discount, $baseAmount), 2); // Redondea a 2 decimales y asegura que no exceda el importe base.
    }
}

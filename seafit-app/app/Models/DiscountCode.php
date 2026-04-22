<?php

/**
 * Modelo Eloquent de códigos de descuento.
 * Define reglas de validez, uso y cálculo de descuentos.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo de códigos de descuento administrables desde el panel.
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
     * Scope para buscar por código.
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
     * Comprueba si un usuario puede usarlo en un contexto.
     */
    public function canBeUsedBy(User $user, string $context = 'registro'): bool
    {
        if (!$this->isActiveNow()) {
            return false;
        }

        if (!$this->one_use_per_user) {
            return true;
        }

        return !$this->redemptions()
            ->where('user_id', $user->id)
            ->where('context', $context)
            ->exists();
    }

    /**
     * Marca el código como usado y guarda el historial.
     */
    public function markUsed(User $user, string $context = 'registro', ?float $amount = null): void
    {
        $this->increment('used_count');

        $this->redemptions()->create([
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
        $baseAmount = max($baseAmount, 0);

        $discount = $this->type === 'percent'
            ? ($baseAmount * ((float) $this->value / 100))
            : (float) $this->value;

        return round(min($discount, $baseAmount), 2);
    }
}

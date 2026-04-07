<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountCode extends Model
{
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

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $model->code = strtoupper(trim($model->code));
        });
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', strtoupper(trim($code)));
    }

    public function isActiveNow(): bool
    {
        $now = now();

        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        if (!is_null($this->max_uses) && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

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
}

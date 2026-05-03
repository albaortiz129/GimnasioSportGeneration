<?php

/**
 * Relaciones y capacidades de facturación.
 */
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    /**
     * Campos que se pueden guardar directamente.
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'dni',
        'fecha_nacimiento',
        'telefono',
        'email',
        'domicilio',
        'tarifa',
        'metodo_pago',
        'password',
        'is_admin',
        'must_change_password',
        'payment_status',
        'next_payment_at',
        'last_manual_payment_at',
        'manual_payment_note',
    ];

    /**
     * Campos que no se muestran al convertir el usuario a JSON/array.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Define la conversión de tipos para los atributos del modelo
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'must_change_password' => 'boolean',
            'payment_status' => 'string',
            'next_payment_at' => 'date',
            'last_manual_payment_at' => 'datetime',
            'manual_payment_methods' => 'array',
        ];
    }

    /**
     * Normaliza el email al guardar y al leer:
     */
    protected function email(): Attribute
    {
        // Siempre en minúsculas para evitar duplicados.
        // Sin espacios al principio/final.
        return Attribute::make(
            get: fn($value) => is_string($value) ? strtolower(trim($value)) : $value,
            set: fn($value) => is_string($value) ? strtolower(trim($value)) : $value,
        );
    }

    /**
     * Normaliza el DNI para mantener formato consistente.
     */
    protected function dni(): Attribute
    {
        return Attribute::make(
            get: fn($value) => is_string($value) ? strtoupper(trim($value)) : $value,
            set: fn($value) => is_string($value) ? strtoupper(trim($value)) : $value,
        );
    }

    /**
     * Un usuario puede reservar varias clases y una clase puede tener varios usuarios.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(GymClass::class, 'clase_user', 'user_id', 'clase_id');
    }

    /**
     * Historial completo de descuentos usados por el usuario.
     */
    public function discountRedemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    /**
     * Último descuento aplicado por fecha de uso.
     */
    public function latestDiscountRedemption(): HasOne
    {
        return $this->hasOne(DiscountRedemption::class)->latestOfMany('applied_at');
    }

    /**
     * Indica si el usuario tiene el plan activo para usar servicios.
     */
    public function isPlanActive(): bool
    {
        // Los administradores no tienen plan de socio.
        if ($this->is_admin) {
            return false;
        }

        // Si el pago no está al día, no está activo.
        if ($this->payment_status !== 'al_dia') {
            return false;
        }

        // Si la baja está programada, se mantiene activo hasta la fecha de fin del período.
        if ($this->tarifa === 'cancelada') {
            if (!$this->next_payment_at) {
                return false;
            }

            $fechaFin = Carbon::parse($this->next_payment_at)->startOfDay();
            $hoy = now()->startOfDay();

            return $fechaFin->gte($hoy);
        }

        if ($this->next_payment_at) {
            // Se parsea siempre a fecha para evitar errores si viene como string.
            $fechaProximoPago = Carbon::parse($this->next_payment_at)->startOfDay();
            $hoy = now()->startOfDay();

            if ($fechaProximoPago->lt($hoy)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Estado del plan para mostrar en vistas.
     */
    public function planStatusText(): string
    {
        if ($this->is_admin) {
            return 'administrador';
        }

        return match ($this->payment_status) {
            'al_dia' => 'activa',
            'pendiente' => 'pendiente',
            'impagado' => 'impagada',
            default => 'inactiva',
        };
    }

}

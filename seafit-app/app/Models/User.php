<?php

/**
 * Modelo Eloquent de usuarios/socios con relaciones y capacidades de facturacion.
 */
namespace App\Models;

use Carbon\Carbon;
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
     * Laravel convierte estos campos al tipo correcto automaticamente.
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
     * Un usuario puede reservar varias clases y una clase puede tener varios usuarios.
     */
    public function classes(): BelongsToMany
    {
        // Se indican claves del pivote manualmente para evitar que Laravel use gym_class_id.
        return $this->belongsToMany(GymClass::class, 'clase_user', 'user_id', 'clase_id');
    }

    /**
     * Historial completo de descuentos usados por este usuario.
     */
    public function discountRedemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    /**
     * Ultimo descuento aplicado por fecha de uso.
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

        if ($this->tarifa === 'cancelada') {
            return false;
        }

        if ($this->payment_status !== 'al_dia') {
            return false;
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
     * Texto corto del estado del plan para mostrar en vistas.
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

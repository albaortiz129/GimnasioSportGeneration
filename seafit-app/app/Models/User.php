<?php

/**
 * Modelo Eloquent de usuarios/socios con relaciones y capacidades de facturacion.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        ];
    }

    /**
     * Un usuario puede reservar varias clases y una clase puede tener varios usuarios.
     */
    public function clases(): BelongsToMany
    {
        return $this->belongsToMany(Clase::class, 'clase_user');
    }
}

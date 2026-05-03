<?php

/**
 * Guarda datos de clase y su relación con usuarios.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GymClass extends Model
{
    use HasFactory;

    protected $table = 'clases';

    /**
     * Campos que se pueden guardar directamente con create() o update().
     */
    protected $fillable = [
        'nombre',
        'instructor',
        'sala',
        'hora_inicio',
        'dia_semana',
        'capacidad_max',
        'descripcion',
        'imagen',
    ];

    /**
     * Una clase puede tener muchos usuarios inscritos y un usuario muchas clases.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clase_user', 'clase_id', 'user_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    use HasFactory;

    protected $table = 'clases';

    protected $fillable = [
        'nombre', 
        'instructor', 
        'sala', 
        'hora_inicio', 
        'dia_semana', 
        'capacidad_max', 
        'descripcion', 
        'imagen'
    ];

    /**
     * RELACIÓN: Una clase puede tener muchos usuarios (socios) inscritos.
     */
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'clase_user');
    }
}
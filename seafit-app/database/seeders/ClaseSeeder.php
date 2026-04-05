<?php

/**
 * Seeder de clases: genera la oferta semanal inicial para pruebas y arranque.
 */
namespace Database\Seeders;

use App\Models\Clase;
use Illuminate\Database\Seeder;

class ClaseSeeder extends Seeder
{
    /**
     * Crea un conjunto base de clases para varios dias de la semana.
     */
    public function run(): void
    {
        $clases = [
            [
                'nombre' => 'Yoga Flow',
                'instructor' => 'Lucía Méndez',
                'sala' => 'Sala Zen',
                'hora_inicio' => '09:00:00',
                'dia_semana' => 'Lunes',
                'capacidad_max' => 15,
                'descripcion' => 'Clase de yoga suave para empezar la semana con energía.',
            ],
            [
                'nombre' => 'Spinning Avanzado',
                'instructor' => 'Sergio Ciclo',
                'sala' => 'Sala Ciclo',
                'hora_inicio' => '10:30:00',
                'dia_semana' => 'Lunes',
                'capacidad_max' => 20,
                'descripcion' => 'Súbete a la bici a máxima potencia.',
            ],
            [
                'nombre' => 'Crossfit Init',
                'instructor' => 'Marc Fuerza',
                'sala' => 'Box',
                'hora_inicio' => '08:30:00',
                'dia_semana' => 'Martes',
                'capacidad_max' => 12,
                'descripcion' => 'Iniciación a movimientos de fuerza.',
            ],
            [
                'nombre' => 'HIIT Intenso',
                'instructor' => 'Carlos Fit',
                'sala' => 'Zona Funcional',
                'hora_inicio' => '18:00:00',
                'dia_semana' => 'Miércoles',
                'capacidad_max' => 20,
                'descripcion' => 'Quema grasa y mejora tu resistencia.',
            ],
            [
                'nombre' => 'Zumba Party',
                'instructor' => 'Sonia Ritmo',
                'sala' => 'Sala 1',
                'hora_inicio' => '19:00:00',
                'dia_semana' => 'Jueves',
                'capacidad_max' => 25,
                'descripcion' => 'Diversión y cardio al ritmo de la música.',
            ],
            [
                'nombre' => 'Spinning Pro',
                'instructor' => 'Sergio Ciclo',
                'sala' => 'Sala Ciclo',
                'hora_inicio' => '19:15:00',
                'dia_semana' => 'Viernes',
                'capacidad_max' => 25,
                'descripcion' => 'Etapas virtuales a máxima potencia.',
            ],
        ];

        foreach ($clases as $clase) {
            Clase::create($clase);
        }
    }
}


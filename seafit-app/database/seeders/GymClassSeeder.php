<?php

/**
 * Seeder de clases: crea la oferta semanal inicial de SeaFit.
 */
namespace Database\Seeders;

use App\Models\GymClass;
use Illuminate\Database\Seeder;

class GymClassSeeder extends Seeder
{
    /**
     * Inserta clases base para pruebas y entorno local.
     */
    public function run(): void
    {
        // Datos de clases como ejemplos.
        $clases = [
            [
                'nombre' => 'Yoga Flow',
                'instructor' => 'Lucia Mendez',
                'sala' => 'Sala Zen',
                'hora_inicio' => '09:00:00',
                'dia_semana' => 'Lunes',
                'capacidad_max' => 15,
                'descripcion' => 'Clase de yoga suave para empezar la semana con energia.',
            ],
            [
                'nombre' => 'Spinning Avanzado',
                'instructor' => 'Sergio Ciclo',
                'sala' => 'Sala Ciclo',
                'hora_inicio' => '10:30:00',
                'dia_semana' => 'Lunes',
                'capacidad_max' => 20,
                'descripcion' => 'Subete a la bici a maxima potencia.',
            ],
            [
                'nombre' => 'Crossfit Init',
                'instructor' => 'Marc Fuerza',
                'sala' => 'Box',
                'hora_inicio' => '08:30:00',
                'dia_semana' => 'Martes',
                'capacidad_max' => 12,
                'descripcion' => 'Iniciacion a movimientos de fuerza.',
            ],
            [
                'nombre' => 'HIIT Intenso',
                'instructor' => 'Carlos Fit',
                'sala' => 'Zona Funcional',
                'hora_inicio' => '18:00:00',
                'dia_semana' => 'Miercoles',
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
                'descripcion' => 'Diversion y cardio al ritmo de la musica.',
            ],
            [
                'nombre' => 'Spinning Pro',
                'instructor' => 'Sergio Ciclo',
                'sala' => 'Sala Ciclo',
                'hora_inicio' => '19:15:00',
                'dia_semana' => 'Viernes',
                'capacidad_max' => 25,
                'descripcion' => 'Etapas virtuales a maxima potencia.',
            ],
        ];

        // Inserta cada clase en la tabla clases.
        foreach ($clases as $clase) {
            GymClass::create($clase);
        }
    }
}

<?php

/**
 * Controlador de servicios.
 * Carga clases por dia para las vistas de servicios y agenda.
 */
namespace App\Http\Controllers;

use App\Models\GymClass;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Muestra la pagina principal de servicios filtrada por dia.
     */
    public function index(Request $request)
    {
        $diaSeleccionado = $request->query('dia', 'Lunes');
        $clases = $this->getClassesByDay($diaSeleccionado);

        return view('services.index', compact('clases', 'diaSeleccionado'));
    }

    /**
     * Muestra la agenda semanal filtrada por dia.
     */
    public function agenda(Request $request)
    {
        $diaSeleccionado = $request->query('dia', 'Lunes');
        $clases = $this->getClassesByDay($diaSeleccionado);

        return view('services.schedule', compact('clases', 'diaSeleccionado'));
    }

    /**
     * Query comun para obtener clases del dia seleccionado.
     */
    private function getClassesByDay(string $diaSeleccionado)
    {
        // Soporta variantes antiguas/nuevas del mismo dia.
        $variantesDia = match ($diaSeleccionado) {
            'Miércoles' => ['Miércoles', 'Miercoles'],
            'Miercoles' => ['Miércoles', 'Miercoles'],
            'Sábado' => ['Sábado', 'Sabado'],
            'Sabado' => ['Sábado', 'Sabado'],
            default => [$diaSeleccionado],
        };

        // Devuelve las clases ordenadas por hora.
        return GymClass::whereIn('dia_semana', $variantesDia)
            ->orderBy('hora_inicio', 'asc')
            ->get();
    }
}


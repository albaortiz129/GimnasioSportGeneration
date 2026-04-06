<?php

/**
 * Controlador de servicios.
 * Carga clases por dia para las vistas de servicios y agenda.
 */
namespace App\Http\Controllers;

use App\Models\Clase;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    /**
     * Muestra la pagina principal de servicios filtrada por dia.
     */
    public function index(Request $request)
    {
        $diaSeleccionado = $request->query('dia', 'Lunes');
        $clases = $this->obtenerClasesPorDia($diaSeleccionado);

        return view('servicios.servicios', compact('clases', 'diaSeleccionado'));
    }

    /**
     * Muestra la agenda semanal filtrada por dia.
     */
    public function agenda(Request $request)
    {
        $diaSeleccionado = $request->query('dia', 'Lunes');
        $clases = $this->obtenerClasesPorDia($diaSeleccionado);

        return view('servicios.agenda', compact('clases', 'diaSeleccionado'));
    }

    /**
     * Query comun para obtener clases del dia seleccionado.
     */
    private function obtenerClasesPorDia(string $diaSeleccionado)
    {
        $variantesDia = match ($diaSeleccionado) {
            'Miércoles' => ['Miércoles', 'Miercoles'],
            'Miercoles' => ['Miércoles', 'Miercoles'],
            'Sábado' => ['Sábado', 'Sabado'],
            'Sabado' => ['Sábado', 'Sabado'],
            default => [$diaSeleccionado],
        };

        return Clase::whereIn('dia_semana', $variantesDia)
            ->orderBy('hora_inicio', 'asc')
            ->get();
    }
}


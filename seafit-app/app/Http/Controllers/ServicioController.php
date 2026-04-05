<?php

/**
 *Controlador de servicios: carga clases por dia para vistas de servicios y agenda.
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
     * Punto unico de consulta para evitar duplicar la misma query.
     */
    private function obtenerClasesPorDia(string $diaSeleccionado)
    {
        return Clase::where('dia_semana', $diaSeleccionado)
            ->orderBy('hora_inicio', 'asc')
            ->get();
    }
}


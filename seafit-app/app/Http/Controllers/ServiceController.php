<?php

/**
 * Controlador de servicios.
 * Carga clases por día para las vistas de servicios y agenda.
 */
namespace App\Http\Controllers;

use App\Models\GymClass;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Muestra la página principal de servicios filtrada por día.
     */
    public function index(Request $request)
    {
        $diaSeleccionado = $request->query('dia', 'Lunes');
        $clases = $this->getClassesByDay($diaSeleccionado);

        return view('services.index', compact('clases', 'diaSeleccionado'));
    }

    /**
     * Muestra la agenda semanal filtrada por día.
     */
    public function agenda(Request $request)
    {
        $diaSeleccionado = $request->query('dia', 'Lunes');
        $clases = $this->getClassesByDay($diaSeleccionado);

        return view('services.schedule', compact('clases', 'diaSeleccionado'));
    }

    /**
     * Query común para obtener clases del día seleccionado.
     */
    private function getClassesByDay(string $diaSeleccionado)
    {
        // Soporta variantes antiguas/nuevas del mismo día.
        $variantesDia = match ($diaSeleccionado) {
            'Miércoles' => ['Miércoles', 'Miercoles'],
            'Miercoles' => ['Miércoles', 'Miercoles'],
            'Sábado' => ['Sábado', 'Sabado'],
            'Sabado' => ['Sábado', 'Sabado'],
            default => [$diaSeleccionado],
        };

        // Devuelve las clases ordenadas por hora.
        $clases = GymClass::whereIn('dia_semana', $variantesDia)
            ->orderBy('hora_inicio', 'asc')
            ->get();

        // Calcula columnas de solape para que el calendario no pise tarjetas.
        return $this->applyCalendarLayout($clases);
    }

    /**
     * Calcula posición y columnas de cada clase para pintar solapes sin sobreescritura.
     * Se asume duración de 1 hora por clase.
     */
    private function applyCalendarLayout($clases)
    {
        $eventos = $clases->values()
            ->map(function ($clase) {
                $hora = (int) substr((string) $clase->hora_inicio, 0, 2);
                $minutos = (int) substr((string) $clase->hora_inicio, 3, 2);
                $inicio = ($hora * 60) + $minutos;
                $fin = $inicio + 60;

                return [
                    'model' => $clase,
                    'start' => $inicio,
                    'end' => $fin,
                ];
            })
            ->sortBy(fn($evento) => [$evento['start'], $evento['end'], $evento['model']->id])
            ->values()
            ->all();

        $grupos = [];
        $grupoActual = [];
        $finGrupoActual = null;

        foreach ($eventos as $evento) {
            if (empty($grupoActual)) {
                $grupoActual[] = $evento;
                $finGrupoActual = $evento['end'];
                continue;
            }

            // Si empieza antes de que termine el grupo, hay cadena de solape.
            if ($evento['start'] < $finGrupoActual) {
                $grupoActual[] = $evento;
                $finGrupoActual = max($finGrupoActual, $evento['end']);
                continue;
            }

            $grupos[] = $grupoActual;
            $grupoActual = [$evento];
            $finGrupoActual = $evento['end'];
        }

        if (!empty($grupoActual)) {
            $grupos[] = $grupoActual;
        }

        foreach ($grupos as $grupo) {
            $activos = [];
            $columnasLibres = [];
            $siguienteColumna = 0;
            $maxColumnas = 1;
            $asignadas = [];

            foreach ($grupo as $evento) {
                foreach ($activos as $idx => $activo) {
                    if ($activo['end'] <= $evento['start']) {
                        $columnasLibres[] = $activo['col'];
                        unset($activos[$idx]);
                    }
                }

                sort($columnasLibres);

                if (!empty($columnasLibres)) {
                    $columna = array_shift($columnasLibres);
                } else {
                    $columna = $siguienteColumna;
                    $siguienteColumna++;
                }

                $activos[] = [
                    'end' => $evento['end'],
                    'col' => $columna,
                ];

                $maxColumnas = max($maxColumnas, $siguienteColumna, count($activos));
                $asignadas[spl_object_id($evento['model'])] = $columna;
            }

            foreach ($grupo as $evento) {
                $clase = $evento['model'];
                $offsetMinutos = max(0, $evento['start'] - (8 * 60));

                $clase->layout_col = (int) ($asignadas[spl_object_id($clase)] ?? 0);
                $clase->layout_cols = (int) max($maxColumnas, 1);
                $clase->layout_top = (int) round($offsetMinutos * (100 / 60));
                $clase->layout_height = 90;
            }
        }

        return $clases;
    }
}


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
     * Obtiene las clases del día seleccionado.
     */
    private function getClassesByDay(string $diaSeleccionado)
    {
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

        // Calcula columnas para que el calendario no pise tarjetas.
        return $this->applyCalendarLayout($clases);
    }

    /**
     * Calcula posición y columnas de cada clase.
     * Duración de 1 hora por clase normalmente.
     */
    private function applyCalendarLayout($clases)
    {
        $eventos = $clases->values() // Obtiene los valores de la colección de clases.
            ->map(function ($clase) { // Mapea cada clase a un evento.
                $hora = (int) substr((string) $clase->hora_inicio, 0, 2); // Obtiene la hora de la clase.
                $minutos = (int) substr((string) $clase->hora_inicio, 3, 2); // Obtiene los minutos de la clase.
                $inicio = ($hora * 60) + $minutos; // Calcula el inicio en minutos.
                $fin = $inicio + 60; // Calcula el fin en minutos.
    
                return [
                    'model' => $clase, // Modelo de clase.
                    'start' => $inicio, // Inicio en minutos.
                    'end' => $fin, // Fin en minutos.
                ];
            })
            ->sortBy(fn($evento) => [$evento['start'], $evento['end'], $evento['model']->id]) // Ordena los eventos por inicio, fin y modelo.
            ->values()
            ->all();

        $grupos = []; // Grupos de eventos.
        $grupoActual = []; // Grupo actual.
        $finGrupoActual = null; // Fin del grupo actual.

        foreach ($eventos as $evento) { // Itera sobre los eventos.
            if (empty($grupoActual)) { // Si el grupo actual está vacío.
                $grupoActual[] = $evento; // Añade el evento al grupo actual.
                $finGrupoActual = $evento['end']; // Actualiza el fin del grupo actual.
                continue; // Continúa con el siguiente evento.
            }

            if ($evento['start'] < $finGrupoActual) { // Si el evento empieza antes de que termine el grupo.
                $grupoActual[] = $evento; // Añade el evento al grupo actual.
                $finGrupoActual = max($finGrupoActual, $evento['end']); // Actualiza el fin del grupo actual.
                continue; // Continúa con el siguiente evento.
            }

            $grupos[] = $grupoActual; // Añade el grupo actual a los grupos.
            $grupoActual = [$evento]; // Reinicia el grupo actual.
            $finGrupoActual = $evento['end']; // Reinicia el fin del grupo actual.
        }

        if (!empty($grupoActual)) { // Si el grupo actual no está vacío.
            $grupos[] = $grupoActual;
        }

        foreach ($grupos as $grupo) { // Itera sobre los grupos.
            $activos = []; // Eventos activos.
            $columnasLibres = []; // Columnas libres.
            $siguienteColumna = 0; // Siguiente columna disponible.
            $maxColumnas = 1; // Máximo de columnas.
            $asignadas = []; // Columnas asignadas.

            foreach ($grupo as $evento) { // Itera sobre el grupo.
                foreach ($activos as $idx => $activo) { // Itera sobre los eventos activos.
                    if ($activo['end'] <= $evento['start']) { // Si el evento termina antes de que empiece el siguiente.
                        $columnasLibres[] = $activo['col']; // Añade la columna a las columnas libres.
                        unset($activos[$idx]); // Elimina el evento activo.
                    }
                }

                sort($columnasLibres); // Ordena las columnas libres.

                if (!empty($columnasLibres)) { // Si hay columnas libres.
                    $columna = array_shift($columnasLibres); // Obtiene la primera columna libre.
                } else { // Si no hay columnas libres.
                    $columna = $siguienteColumna; // Obtiene la siguiente columna disponible.
                    $siguienteColumna++; // Incrementa la siguiente columna disponible.
                }

                $activos[] = [ // Añade el evento activo.
                    'end' => $evento['end'], // Fin del evento.
                    'col' => $columna, // Columna del evento.
                ];

                $maxColumnas = max($maxColumnas, $siguienteColumna, count($activos)); // Actualiza el máximo de columnas.
                $asignadas[spl_object_id($evento['model'])] = $columna; // Asigna la columna al evento.
            }

            foreach ($grupo as $evento) { // Itera sobre el grupo.
                $clase = $evento['model']; // Obtiene el modelo de clase.
                $offsetMinutos = max(0, $evento['start'] - (8 * 60)); // Calcula el offset en minutos.

                $clase->layout_col = (int) ($asignadas[spl_object_id($clase)] ?? 0); // Asigna la columna al evento.
                $clase->layout_cols = (int) max($maxColumnas, 1); // Asigna el máximo de columnas al evento.
                $clase->layout_top = (int) round($offsetMinutos * (100 / 60)); // Asigna el offset en minutos al evento.
                $clase->layout_height = 90; // Asigna la altura al evento.
            }
        }

        return $clases;
    }
}


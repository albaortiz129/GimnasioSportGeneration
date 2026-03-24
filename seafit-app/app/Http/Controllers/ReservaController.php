<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clase;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    public function reservar(Request $request, $id)
    {
        $clase = Clase::findOrFail($id);
        $user = Auth::user();

        // 1. Cargamos las clases del usuario para evitar errores de null
        // y poder usar el método 'contains' correctamente.
        $user->load('clases');

        // 2. Verificar si el usuario ya está apuntado (Evitamos duplicados)
        if ($user->clases->contains($clase->id)) {
            return back()->with('info', 'Ya tienes una reserva para esta clase.');
        }

        // 3. Gestión de Disponibilidad: Verificar si hay plazas libres
        if ($clase->capacidad_max <= 0) {
            return back()->with('error', 'Lo sentimos, esta clase ya está llena.');
        }

        // 4. Realizar la reserva en la tabla pivote (clase_user)
        $user->clases()->attach($clase->id);

        // 5. Restar una plaza de la disponibilidad real
        $clase->decrement('capacidad_max');

        return back()->with('success', '¡Reserva confirmada! Te quedan ' . $clase->capacidad_max . ' plazas libres.');
    }

    public function cancelar($id)
    {
        $clase = Clase::findOrFail($id);
        $user = Auth::user();

        // Desvincular al usuario de la clase
        $user->clases()->detach($clase->id);

        // DEVOLVER LA PLAZA: Sumamos 1 a la capacidad máxima
        $clase->increment('capacidad_max');

        return back()->with('success', 'Plaza liberada correctamente.');
    }
}
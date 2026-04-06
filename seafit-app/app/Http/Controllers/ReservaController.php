<?php

/**
 * Controlador de reservas: alta y cancelacion de plazas en clases.
 */
namespace App\Http\Controllers;

use App\Models\Clase;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    /**
     * Reserva una plaza de clase para el usuario autenticado.
     */
    public function reservar($id)
    {
        $clase = Clase::findOrFail($id);
        $user = Auth::user();

        // Carga reservas actuales para evitar reservas duplicadas.
        $user->load('clases');

        if ($user->clases->contains($clase->id)) {
            return back()->with('info', 'Ya tienes una reserva para esta clase.');
        }

        if ($clase->capacidad_max <= 0) {
            return back()->with('error', 'Lo sentimos, esta clase ya está llena.');
        }

        // Guarda la reserva y descuenta una plaza disponible.
        $user->clases()->attach($clase->id);
        $clase->decrement('capacidad_max');

        return back()->with('success', '¡Reserva confirmada! Te quedan ' . $clase->capacidad_max . ' plazas libres.');
    }

    /**
     * Cancela una reserva y devuelve la plaza a la clase.
     */
    public function cancelar($id)
    {
        $clase = Clase::findOrFail($id);
        $user = Auth::user();

        // Solo devuelve plaza si la reserva existia.
        $teniaReserva = $user->clases()->where('clase_id', $clase->id)->exists();
        $user->clases()->detach($clase->id);

        if ($teniaReserva) {
            $clase->increment('capacidad_max');
        }

        return back()->with('success', 'Plaza liberada correctamente.');
    }
}


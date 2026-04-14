<?php

/**
 * Controlador de reservas: alta y cancelacion de plazas en clases.
 */
namespace App\Http\Controllers;

use App\Models\GymClass;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Reserva una plaza de clase para el usuario autenticado.
     */
    public function book($id)
    {
        // Clase objetivo y usuario actual.
        $clase = GymClass::findOrFail($id);
        $user = Auth::user();

        // Carga reservas actuales para evitar reservas duplicadas.
        $user->load('classes');

        if ($user->classes->contains($clase->id)) {
            return back()->with('info', 'Ya tienes una reserva para esta clase.');
        }

        if ($clase->capacidad_max <= 0) {
            return back()->with('error', 'Lo sentimos, esta clase ya está llena.');
        }

        // Guarda la reserva y descuenta una plaza disponible.
        $user->classes()->attach($clase->id);
        $clase->decrement('capacidad_max');

        return back()->with('success', '¡Reserva confirmada! Te quedan ' . $clase->capacidad_max . ' plazas libres.');
    }

    /**
     * Cancela una reserva y devuelve la plaza a la clase.
     */
    public function cancel($id)
    {
        // Clase objetivo y usuario actual.
        $clase = GymClass::findOrFail($id);
        $user = Auth::user();

        // Solo devuelve plaza si la reserva existia.
        $teniaReserva = $user->classes()->where('clase_id', $clase->id)->exists();
        $user->classes()->detach($clase->id);

        if ($teniaReserva) {
            $clase->increment('capacidad_max');
        }

        return back()->with('success', 'Plaza liberada correctamente.');
    }
}


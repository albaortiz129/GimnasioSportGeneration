<?php

/**
 * Controlador de reservas: alta y cancelacion de plazas en clases.
 */
namespace App\Http\Controllers;

use App\Models\GymClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Reserva una plaza de clase para el usuario autenticado.
     */
    public function book($id)
    {
        $user = Auth::user();

        $resultado = DB::transaction(function () use ($id, $user) {
            // Bloqueo de fila para evitar sobrecupo por peticiones simultaneas.
            $clase = GymClass::query()->lockForUpdate()->findOrFail($id);

            if ($user->classes()->where('clase_id', $clase->id)->exists()) {
                return ['status' => 'duplicate'];
            }

            if ($clase->capacidad_max <= 0) {
                return ['status' => 'full'];
            }

            $user->classes()->attach($clase->id);
            $clase->decrement('capacidad_max');

            return [
                'status' => 'ok',
                'plazas' => max((int) $clase->capacidad_max, 0),
            ];
        });

        if ($resultado['status'] === 'duplicate') {
            return back()->with('info', 'Ya tienes una reserva para esta clase.');
        }

        if ($resultado['status'] === 'full') {
            return back()->with('error', 'Lo sentimos, esta clase ya esta llena.');
        }

        return back()->with('success', 'Reserva confirmada. Te quedan ' . $resultado['plazas'] . ' plazas libres.');
    }

    /**
     * Cancela una reserva y devuelve la plaza a la clase.
     */
    public function cancel($id)
    {
        $user = Auth::user();

        $teniaReserva = DB::transaction(function () use ($id, $user) {
            // Mismo bloqueo para mantener capacidad coherente.
            $clase = GymClass::query()->lockForUpdate()->findOrFail($id);

            $tenia = $user->classes()->where('clase_id', $clase->id)->exists();

            if (!$tenia) {
                return false;
            }

            $user->classes()->detach($clase->id);
            $clase->increment('capacidad_max');

            return true;
        });

        if (!$teniaReserva) {
            return back()->with('info', 'No tenias una reserva activa para esa clase.');
        }

        return back()->with('success', 'Plaza liberada correctamente.');
    }
}

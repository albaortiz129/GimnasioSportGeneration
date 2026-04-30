<?php

/**
 * Controlador de reservas: alta y cancelación de plazas en clases.
 */
namespace App\Http\Controllers;

use App\Models\GymClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * Reserva una plaza de clase para el usuario autenticado.
     */
    public function book($id)
    {
        $user = Auth::user(); // Obtiene el usuario autenticado.

        $resultado = DB::transaction(function () use ($id, $user) { // Inicia una transacción para asegurar la integridad de los datos.
            // Busca la clase por ID y la bloquea para evitar que dos usuarios ocupen la última plaza al mismo tiempo.
            $clase = GymClass::query()->lockForUpdate()->findOrFail($id);

            if ($user->classes()->where('clase_id', $clase->id)->exists()) { // Comprueba si el usuario ya tiene una reserva para esta clase.
                return ['status' => 'duplicate'];
            }

            if ($clase->capacidad_max <= 0) { // Comprueba si la clase está llena.
                return ['status' => 'full'];
            }

            $user->classes()->attach($clase->id); // Añade la reserva.
            $clase->decrement('capacidad_max'); // Reduce el número de plazas disponibles.

            return [
                'status' => 'ok',
                'plazas' => max((int) $clase->capacidad_max, 0),
                // Datos de clase para email de confirmación de reserva.
                'clase' => [
                    'nombre' => (string) $clase->nombre,
                    'dia' => (string) $clase->dia_semana,
                    'hora' => substr((string) $clase->hora_inicio, 0, 5),
                    'sala' => (string) $clase->sala,
                    'instructor' => (string) $clase->instructor,
                ],
            ];
        });

        if ($resultado['status'] === 'duplicate') { // Muestra un mensaje de error si el usuario ya tiene una reserva para esta clase.
            return back()->with('info', 'Ya tienes una reserva para esta clase.');
        }

        if ($resultado['status'] === 'full') { // Muestra un mensaje de error si la clase está llena.
            return back()->with('error', 'Lo sentimos, esta clase ya está llena.');
        }

        try { // Intenta enviar el email de confirmación de reserva.
            $datosClase = (array) ($resultado['clase'] ?? []);
            $hora = (string) ($datosClase['hora'] ?? '');
            $horaFin = $hora !== ''
                ? Carbon::createFromFormat('H:i', $hora)->addHour()->format('H:i') // Calcula la hora de fin de la clase.
                : '';
            // Envía el correo con los datos de la reserva.
            Mail::send('emails.class-booked', [
                'nombre' => $user->nombre,
                'claseNombre' => (string) ($datosClase['nombre'] ?? 'Clase'),
                'diaSemana' => (string) ($datosClase['dia'] ?? 'Sin día'),
                'horaInicio' => $hora !== '' ? $hora : 'Sin hora',
                'horaFin' => $horaFin !== '' ? $horaFin : null,
                'sala' => (string) ($datosClase['sala'] ?? 'Sin sala'),
                'instructor' => (string) ($datosClase['instructor'] ?? 'Sin instructor'),
            ], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Reserva confirmada - SeaFit');
            });
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'class_id' => $id,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Reserva confirmada. Quedan ' . $resultado['plazas'] . ' plazas libres.');
    }

    /**
     * Cancela una reserva y devuelve la plaza a la clase.
     */
    public function cancel($id)
    {
        $user = Auth::user();

        $teniaReserva = DB::transaction(function () use ($id, $user) { // Inicia una transacción para asegurar la integridad de los datos.
            $clase = GymClass::query()->lockForUpdate()->findOrFail($id); // Bloquea la clase para que si 2 personas cancelan o reservan a la vez, las plazas no se sumen o resten mal.

            $tenia = $user->classes()->where('clase_id', $clase->id)->exists(); // Comprueba si el usuario ya tiene una reserva para esta clase.

            if (!$tenia) { // Si el usuario no tiene una reserva para esta clase, devuelve false.
                return false;
            }

            $user->classes()->detach($clase->id); // Elimina la reserva.
            $clase->increment('capacidad_max'); // Aumenta el número de plazas disponibles.

            return true;
        });

        if (!$teniaReserva) { // Muestra un mensaje de error si el usuario no tiene una reserva activa para esa clase.
            return back()->with('info', 'No tenías una reserva activa para esa clase.');
        }

        return back()->with('success', 'Plaza liberada correctamente.'); // Muestra un mensaje de éxito si la reserva se ha cancelado correctamente.
    }
}

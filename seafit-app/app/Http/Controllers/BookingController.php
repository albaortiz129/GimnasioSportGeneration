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
        $user = Auth::user();

        $resultado = DB::transaction(function () use ($id, $user) {
            // Bloqueo de fila para evitar sobrecupo por peticiones simultáneas.
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

        if ($resultado['status'] === 'duplicate') {
            return back()->with('info', 'Ya tienes una reserva para esta clase.');
        }

        if ($resultado['status'] === 'full') {
            return back()->with('error', 'Lo sentimos, esta clase ya está llena.');
        }

        // Email informativo de reserva confirmada.
        // Si falla el envío, no se revierte la reserva.
        try {
            $datosClase = (array) ($resultado['clase'] ?? []);
            $hora = (string) ($datosClase['hora'] ?? '');
            $horaFin = $hora !== ''
                ? Carbon::createFromFormat('H:i', $hora)->addHour()->format('H:i')
                : '';

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
            Log::error('Error al enviar correo de reserva de clase.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'class_id' => $id,
                'error' => $e->getMessage(),
            ]);
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
            return back()->with('info', 'No tenías una reserva activa para esa clase.');
        }

        return back()->with('success', 'Plaza liberada correctamente.');
    }
}

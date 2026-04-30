<?php

/**
 * Controlador del formulario de valoración de entrenador personal.
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AssessmentController extends Controller
{
    /**
     * Correo que recibe las solicitudes del formulario de entrenador.
     */
    private const TRAINER_FORM_RECIPIENT = 'aortpul1206@iesfuengirola1.es';

    /**
     * Valida y envía la solicitud de valoración por correo.
     */
    public function send(Request $request)
    {
        // Reglas básicas para evitar envíos vacíos o con formato inválido.
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'objetivo' => 'required|in:perder-peso,ganar-musculo,resistencia,salud',
            'mensaje' => 'nullable|string|max:1200',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'objetivo.required' => 'Debes seleccionar un objetivo.',
            'objetivo.in' => 'El objetivo seleccionado no es válido.',
        ]);

        // Normalización de campos.
        $data['nombre'] = trim((string) $data['nombre']);
        $data['email'] = strtolower(trim((string) $data['email']));
        $data['mensaje'] = trim((string) ($data['mensaje'] ?? ''));

        try {
            // Enviamos un correo simple con todos los datos de la solicitud.
            Mail::send('emails.trainer-request', ['data' => $data], function ($message) use ($data) {
                $message->to(self::TRAINER_FORM_RECIPIENT);
                $message->replyTo($data['email'], $data['nombre']);
                $message->subject('Nueva solicitud de entrenador personal SeaFit');
            });
        } catch (\Throwable $e) {
            // Guardamos el error.
            Log::error('Error al enviar solicitud a un entrenador personal.', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['formulario' => 'No se pudo enviar la solicitud ahora mismo. Inténtalo de nuevo en unos minutos.']);
        }

        return back()->with('exito', 'Solicitud enviada correctamente. Un entrenador te contactará pronto.');
    }
}

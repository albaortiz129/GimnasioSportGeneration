<?php

/**
 * Controlador del formulario de valoración de entrenador personal.
 */
namespace App\Http\Controllers;

class AssessmentController extends Controller
{
    /**
     * Muestra mensaje de confirmación al enviar solicitud.
     */
    public function send()
    {
        // En esta versión se simula el envío y se devuelve el mensaje.
        return back()->with('exito', 'Solicitud enviada correctamente. Un entrenador te contactará pronto.');
    }
}

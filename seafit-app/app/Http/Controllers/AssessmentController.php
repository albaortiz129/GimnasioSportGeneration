<?php

/**
 * Controlador del formulario de valoracion de entrenador personal.
 */
namespace App\Http\Controllers;

class AssessmentController extends Controller
{
    /**
     * Muestra mensaje de confirmacion al enviar solicitud.
     */
    public function send()
    {
        // En esta version se simula envio y se devuelve mensaje.
        return back()->with('exito', 'Solicitud enviada correctamente. Un entrenador te contactara pronto.');
    }
}

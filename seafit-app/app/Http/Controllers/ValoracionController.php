<?php

/**
 * Controlador del formulario de valoracion de entrenador personal.
 */
namespace App\Http\Controllers;

class ValoracionController extends Controller
{
    /**
     * Muestra mensaje de confirmacion al enviar solicitud.
     */
    public function enviar()
    {
        return back()->with('exito', 'Solicitud enviada correctamente. Un entrenador te contactara pronto.');
    }
}

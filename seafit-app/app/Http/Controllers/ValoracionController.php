<?php

/**
 * Controlador del formulario de valoracion de entrenador personal.
 */
namespace App\Http\Controllers;

class ValoracionController extends Controller
{
    /**
     * Recibe la solicitud de valoracion y devuelve confirmacion visual.
     */
    public function enviar()
    {
        return back()->with('exito', '¡Solicitud enviada correctamente! Un entrenador te contactará pronto.');
    }
}


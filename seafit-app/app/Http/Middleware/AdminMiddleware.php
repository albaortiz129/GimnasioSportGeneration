<?php

/**
 * Middleware de autorizacion: restringe rutas administrativas a usuarios con rol admin.
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Permite continuar solo a usuarios administradores.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo deja pasar si hay sesion y rol admin.
        if (auth()->check() && auth()->user()->is_admin) {
            return $next($request);
        }

        // Si no cumple permisos, se devuelve al inicio con mensaje de error.
        return redirect('/')->with('error', 'Acceso denegado. No eres administrador.');
    }
}


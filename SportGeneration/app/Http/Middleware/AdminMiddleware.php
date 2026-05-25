<?php

/**
 * Middleware de autorización: Restringe las rutas administrativas a usuarios con rol de administrador.
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
        // Solo deja pasar si hay sesión y rol de administrador.
        if (auth()->check() && auth()->user()->is_admin) {
            return $next($request);
        }
        // Si no cumple permisos, se devuelve al inicio con un mensaje de error.
        return redirect('/')->with('error', 'Acceso denegado. No eres administrador.');
    }
}

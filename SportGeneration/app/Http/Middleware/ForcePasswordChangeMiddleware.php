<?php

/**
 * Middleware de seguridad para forzar cambio de contraseña inicial.
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChangeMiddleware
{
    /**
     * Permite seguir solo si el usuario ya cambió su contraseña temporal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no aplica la regla, deja pasar.
        if (!$user || $user->is_admin || !$user->must_change_password) {
            return $next($request);
        }

        // Rutas permitidas mientras aún no cambia su clave.
        if ($request->routeIs('password.force.form', 'password.force.update', 'logout')) {
            return $next($request);
        }

        return redirect()->route('password.force.form')
            ->with('warning', 'Debes cambiar tu contraseña temporal antes de continuar.');
    }
}

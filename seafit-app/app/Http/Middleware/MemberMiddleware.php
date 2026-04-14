<?php

/**
 * Middleware de zona socio.
 * Evita que un administrador entre en paginas de socio.
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberMiddleware
{
    /**
     * Si el usuario es admin, lo redirige al panel admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Un admin no debe navegar por la zona de socio.
        if (auth()->check() && auth()->user()->is_admin) {
            return redirect()->route('admin.dashboard')->with('error', 'Esta zona es solo para socios.');
        }

        return $next($request);
    }
}

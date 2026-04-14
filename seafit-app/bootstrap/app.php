<?php

/**
 * Punto de arranque de Laravel.
 * Configura rutas, middleware y manejo global de excepciones.
 */
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'member' => \App\Http\Middleware\MemberMiddleware::class,
            'force.password' => \App\Http\Middleware\ForcePasswordChangeMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Evita la pantalla 419 por CSRF caducado y vuelve al formulario.
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu sesion ha caducado. Recarga la pagina e intentalo de nuevo.',
                ], 419);
            }

            return back()
                ->withInput($request->except('_token'))
                ->with('error', 'Tu sesion ha caducado. Hemos recargado seguridad; vuelve a enviar el formulario.');
        });
    })->create();

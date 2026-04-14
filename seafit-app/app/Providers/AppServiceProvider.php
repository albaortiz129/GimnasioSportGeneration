<?php

/**
 * Provider global de la aplicacion.
 * Se usa para registrar servicios compartidos de Laravel.
 */
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registro de servicios personalizados.
     */
    public function register(): void
    {
        // Registrar bindings o servicios globales personalizados aqui.
    }

    /**
     * Arranque de configuraciones globales.
     */
    public function boot(): void
    {
        // Configuracion global que se ejecuta al arrancar la aplicacion.
    }
}

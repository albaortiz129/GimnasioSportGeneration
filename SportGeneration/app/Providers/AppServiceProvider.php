<?php

/**
 * Provider global de la aplicación.
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
        // Registrar bindings o servicios globales personalizados aquí.
    }

    /**
     * Arranque de configuraciones globales.
     */
    public function boot(): void
    {
        // Configuración global que se ejecuta al arrancar la aplicación.
    }
}

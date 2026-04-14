<?php

/**
 * Rutas de comandos Artisan definidos dentro del proyecto.
 * Aqui se dejan tareas internas que no exponen rutas web.
 */
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Comando de ejemplo de Laravel (no afecta al funcionamiento de la web).
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

<?php
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
 * API: Registro de socios
 */
// Endpoint usado por el formulario React para crear nuevos socios.
Route::post('/registro', [RegistrationController::class, 'register']);

// Comprueba si DNI y email estan libres antes de terminar el registro.
Route::post('/registro/disponibilidad', [RegistrationController::class, 'checkAvailability']);


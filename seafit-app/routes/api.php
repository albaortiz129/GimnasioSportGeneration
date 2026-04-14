<?php

/**
 * Definicion de endpoints API de SeaFit (registro de socios).
 */
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API: Registro de socios
|--------------------------------------------------------------------------
*/
// Endpoint usado por el formulario React para crear nuevos socios.
Route::post('/registro', [RegistrationController::class, 'register']);


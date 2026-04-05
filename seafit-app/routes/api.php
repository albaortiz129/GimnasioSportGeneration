<?php

/**
 * Definicion de endpoints API de SeaFit (registro de socios).
 */
use App\Http\Controllers\RegistroController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API: Registro de socios
|--------------------------------------------------------------------------
*/
Route::post('/registro', [RegistroController::class, 'registrar']);


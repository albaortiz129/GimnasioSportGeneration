<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;

Route::post('/registro', [RegistroController::class, 'registrar']);
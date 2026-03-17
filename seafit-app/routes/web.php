<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ValoracionController;


// --- INICIO ---
Route::get('/', function () {
    return view('welcome');
})->name('home');

// --- PÁGINAS INFORMATIVAS ---
Route::get('/tarifas', function () {
    return view('paginas.tarifas');
})->name('tarifas');

Route::get('/contacto', function () {
    return view('paginas.contacto');
})->name('contacto');

// --- SERVICIOS Y AGENDA ---
// Una sola definición para cada una vinculada al controlador
Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios');
Route::get('/agenda', [ServicioController::class, 'agenda'])->name('agenda');

// --- VALORACIÓN (Entrenador Personal) ---
Route::get('/valoracion', function () {
    return view('servicios.valoracion');
})->name('valoracion');

// --- REGISTRO Y LOGIN ---
Route::get('/registro', function () {
    return view('usuario.registro');
})->name('registro');
Route::post('/api/registro', [RegistroController::class, 'registrar']);

Route::get('/login', function () {
    return view('usuario.iniciosesion');
})->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- RESERVAS Y PERFIL (Protegidos) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', function () {
        // Obtenemos al usuario con sus clases reservadas
        $user = Auth::user()->load('clases'); 
        return view('usuario.perfil', compact('user'));
    })->name('perfil');
    
    Route::post('/reservar/{id}', [ReservaController::class, 'reservar'])->name('clase.reservar');
});


// Esta ruta es la que procesará el botón de valoracion
Route::post('/valoracion', [ValoracionController::class, 'enviar'])->name('valoracion.enviar');
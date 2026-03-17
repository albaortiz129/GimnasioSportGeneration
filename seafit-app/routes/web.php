<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ValoracionController;
use App\Http\Controllers\PagoController;

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
Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios');
Route::get('/agenda', [ServicioController::class, 'agenda'])->name('agenda');

// --- VALORACIÓN (Entrenador Personal) ---
Route::get('/valoracion', function () {
    return view('servicios.valoracion');
})->name('valoracion');
Route::post('/valoracion', [ValoracionController::class, 'enviar'])->name('valoracion.enviar');

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


// =======================================================
// --- ZONA PRIVADA DEL SOCIO (Requiere iniciar sesión) ---
// =======================================================
Route::middleware(['auth'])->group(function () {
    
    // Panel Principal (Perfil)
    Route::get('/perfil', function () {
        $user = Auth::user()->load('clases'); 
        return view('usuario.perfil', compact('user'));
    })->name('perfil');

    // Gestión de Reservas
    Route::get('/mis-reservas', function () {
        $user = Auth::user()->load('clases');
        return view('usuario.mis-reservas', compact('user'));
    })->name('mis.reservas');
    Route::post('/reservar/{id}', [ReservaController::class, 'reservar'])->name('clase.reservar');
    Route::delete('/reservar/{id}', [ReservaController::class, 'cancelar'])->name('clase.cancelar');

    // Gestión de Pago y Facturación
    Route::get('/gestion-pago', [PagoController::class, 'index'])->name('pago.gestion');
    Route::post('/plan/cancelar', [PagoController::class, 'cancelarPlan'])->name('plan.cancelar');
    Route::get('/factura/descargar/{id}', [PagoController::class, 'descargarFactura'])->name('factura.descargar');
    
    // Gestión de Tarjetas / Métodos de Pago
    Route::get('/pago/nuevo', [PagoController::class, 'nuevoMetodo'])->name('pago.nuevo');
    Route::post('/pago/principal', [PagoController::class, 'establecerPrincipal'])->name('pago.principal');
    Route::delete('/pago/eliminar', [PagoController::class, 'eliminarMetodo'])->name('pago.eliminar');

    // Configuracion
    Route::get('/configuracion', function () {
        $user = Auth::user();
        return view('usuario.configuracion', compact('user'));
    })->name('configuracion');

    Route::post('/configuracion/actualizar', function (\Illuminate\Http\Request $request) {
        $user = Auth::user();
        
        // Validamos que los datos sean correctos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'dni' => 'required|string',
            'telefono' => 'required|string',
            'domicilio' => 'required|string|max:255',
        ]);

        // Actualizamos en la base de datos
        $user->update($request->only('nombre', 'email', 'dni', 'telefono', 'domicilio'));

        return back()->with('success', 'Tus datos se han actualizado correctamente.');
    })->name('configuracion.actualizar');
});
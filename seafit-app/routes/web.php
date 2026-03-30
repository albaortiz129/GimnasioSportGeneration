<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ValoracionController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PasswordController;
use Illuminate\Http\Request;

// --- INICIO (Carpeta inicio) ---
Route::get('/', function () {
    return view('inicio.home'); // Cambiado de welcome a home según tu carpeta
})->name('home');

// --- PÁGINAS INFORMATIVAS / SOPORTE (Carpeta soporte y tarifas) ---
Route::get('/tarifas', function () {
    return view('tarifas.tarifas'); // Tu carpeta se llama tarifas
})->name('tarifas');

Route::view('/faq', 'soporte.faq')->name('faq');
Route::view('/contacto', 'soporte.contacto')->name('contacto');
Route::view('/sobre-nosotros', 'soporte.sobre-nosotros')->name('nosotros');
Route::view('/trabaja-con-nosotros', 'soporte.trabaja-con-nosotros')->name('empleo');

// --- SERVICIOS Y AGENDA (Carpeta servicios) ---
Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios');
Route::get('/agenda', [ServicioController::class, 'agenda'])->name('agenda');

Route::get('/valoracion', function () {
    return view('servicios.valoracion');
})->name('valoracion');
Route::post('/valoracion', [ValoracionController::class, 'enviar'])->name('valoracion.enviar');

// --- REGISTRO Y LOGIN (Carpeta usuario) ---
Route::get('/registro', function () {
    return view('usuario.registro');
})->name('registro');

Route::get('/login', function () {
    return view('usuario.iniciosesion');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// =======================================================
// --- ZONA PRIVADA DEL SOCIO (Carpeta usuario) ---
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

    // Gestión de Tarjetas
    Route::get('/pago/nuevo', [PagoController::class, 'nuevoMetodo'])->name('pago.nuevo');
    Route::post('/pago/principal', [PagoController::class, 'establecerPrincipal'])->name('pago.principal');
    Route::delete('/pago/eliminar', [PagoController::class, 'eliminarMetodo'])->name('pago.eliminar');

    // Configuración
    Route::get('/configuracion', function () {
        $user = Auth::user();
        return view('usuario.configuracion', compact('user'));
    })->name('configuracion');

    Route::post('/configuracion/actualizar', function (Request $request) {
        $user = Auth::user();
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'dni' => 'required|string',
            'telefono' => 'required|string',
            'domicilio' => 'required|string|max:255',
        ]);
        $user->update($request->only('nombre', 'email', 'dni', 'telefono', 'domicilio'));
        return back()->with('success', 'Tus datos se han actualizado correctamente.');
    })->name('configuracion.actualizar');

    Route::post('/plan/reanudar', [PagoController::class, 'reanudarPlan'])->name('plan.reanudar');
    Route::post('/perfil/password', [PasswordController::class, 'cambiarPasswordPerfil'])->name('perfil.password');
    Route::post('/pago/guardar', [PagoController::class, 'guardarMetodo'])->name('pago.guardar');
});

// OLVIDE MI CONTRASEÑA (Usa vistas de la carpeta usuario)
Route::get('/recuperar-password', [PasswordController::class, 'mostrarFormularioEmail'])->name('password.request');
Route::post('/recuperar-password', [PasswordController::class, 'enviarEnlace'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordController::class, 'mostrarFormularioReset'])->name('password.reset');
Route::post('/reset-password', [PasswordController::class, 'actualizarPassword'])->name('password.update');

// ENVÍO DE EMPLEO (Real)
Route::post('/trabaja-con-nosotros/enviar', function (Request $request) {
    // Aquí pondremos la lógica del Mailer que te pasé antes
    return back()->with('success', '¡Candidatura enviada con éxito!');
})->name('empleo.enviar');

//Ruta de prueba para Render
Route::get('/healthz', function () {
    return response('ok', 200);
});
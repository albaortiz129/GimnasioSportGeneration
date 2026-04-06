<?php

/**
 * Definicion de rutas web de SeaFit: paginas publicas, privadas y administracion.
 */
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ValoracionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Paginas publicas
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('inicio.home'))->name('home');
Route::get('/tarifas', fn() => view('tarifas.tarifas'))->name('tarifas');

Route::view('/faq', 'soporte.faq')->name('faq');
Route::view('/contacto', 'soporte.contacto')->name('contacto');
Route::view('/sobre-nosotros', 'soporte.sobre-nosotros')->name('nosotros');
Route::view('/trabaja-con-nosotros', 'soporte.trabaja-con-nosotros')->name('empleo');

Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios');
Route::get('/agenda', [ServicioController::class, 'agenda'])->name('agenda');

Route::get('/valoracion', fn() => view('servicios.valoracion'))->name('valoracion');
Route::post('/valoracion', [ValoracionController::class, 'enviar'])->name('valoracion.enviar');

/*
|--------------------------------------------------------------------------
| Autenticacion
|--------------------------------------------------------------------------
*/
Route::get('/registro', fn() => view('usuario.registro'))->name('registro');
Route::get('/login', fn() => view('usuario.iniciosesion'))->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Zona privada de socio
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', function () {
        $user = Auth::user()->load('clases');
        return view('usuario.perfil', compact('user'));
    })->name('perfil');

    Route::get('/mis-reservas', function () {
        $user = Auth::user()->load('clases');
        return view('usuario.mis-reservas', compact('user'));
    })->name('mis.reservas');

    Route::post('/reservar/{id}', [ReservaController::class, 'reservar'])->name('clase.reservar');
    Route::delete('/reservar/{id}', [ReservaController::class, 'cancelar'])->name('clase.cancelar');

    Route::get('/gestion-pago', [PagoController::class, 'index'])->name('pago.gestion');
    Route::post('/plan/cancelar', [PagoController::class, 'cancelarPlan'])->name('plan.cancelar');
    Route::post('/plan/reanudar', [PagoController::class, 'reanudarPlan'])->name('plan.reanudar');
    Route::get('/factura/descargar/{id}', [PagoController::class, 'descargarFactura'])->name('factura.descargar');

    Route::get('/pago/nuevo', [PagoController::class, 'nuevoMetodo'])->name('pago.nuevo');
    Route::post('/pago/principal', [PagoController::class, 'establecerPrincipal'])->name('pago.principal');
    Route::delete('/pago/eliminar', [PagoController::class, 'eliminarMetodo'])->name('pago.eliminar');
    Route::post('/pago/guardar', [PagoController::class, 'guardarMetodo'])->name('pago.guardar');

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

    Route::post('/perfil/password', [PasswordController::class, 'cambiarPasswordPerfil'])->name('perfil.password');
});

/*
|--------------------------------------------------------------------------
| Recuperacion de contrasena
|--------------------------------------------------------------------------
*/
Route::get('/recuperar-password', [PasswordController::class, 'mostrarFormularioEmail'])->name('password.request');
Route::post('/recuperar-password', [PasswordController::class, 'enviarEnlace'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordController::class, 'mostrarFormularioReset'])->name('password.reset');
Route::post('/reset-password', [PasswordController::class, 'actualizarPassword'])->name('password.update');

/*
|--------------------------------------------------------------------------
| Candidaturas (pendiente de implementar)
|--------------------------------------------------------------------------
*/
Route::post('/trabaja-con-nosotros/enviar', function () {
    // Pendiente: integrar mailer real y almacenamiento de candidaturas.
    return back()->with('success', '¡Candidatura enviada con éxito!');
})->name('empleo.enviar');

/*
|--------------------------------------------------------------------------
| Zona admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/usuario/editar/{id}', [AdminController::class, 'edit'])->name('admin.user.edit');
    Route::put('/usuario/actualizar/{id}', [AdminController::class, 'update'])->name('admin.user.update');
    Route::delete('/usuario/eliminar/{id}', [AdminController::class, 'destroy'])->name('admin.user.delete');
});

use Illuminate\Support\Facades\Artisan;

Route::get('/deploy/{token}', function (string $token) {
    abort_unless($token === env('DEPLOY_TOKEN'), 403);

    // Si quieres borrar todo y rehacer BD:
    // $cmd = 'migrate:fresh --seed --force';

    // Si NO quieres borrar datos:
    $cmd = 'migrate --force';

    Artisan::call($cmd);

    return '<pre>' . e("Comando: $cmd\n\n" . Artisan::output()) . '</pre>';
});
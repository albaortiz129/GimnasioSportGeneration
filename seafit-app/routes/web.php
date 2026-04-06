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
    Route::get('/cambiar-password-inicial', [PasswordController::class, 'mostrarFormularioCambioInicial'])
        ->name('password.force.form');
    Route::post('/cambiar-password-inicial', [PasswordController::class, 'cambiarPasswordInicial'])
        ->name('password.force.update');

    Route::middleware(['socio', 'force.password'])->group(function () {
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
        Route::post('/pago/guardar-manual', [PagoController::class, 'guardarMetodoManual'])->name('pago.guardar_manual');
        Route::post('/pago/principal-manual', [PagoController::class, 'principalManual'])->name('pago.principal_manual');
        Route::delete('/pago/eliminar-manual', [PagoController::class, 'eliminarMetodoManual'])->name('pago.eliminar_manual');

        Route::get('/configuracion', function () {
            $user = Auth::user();
            return view('usuario.configuracion', compact('user'));
        })->name('configuracion');

        Route::post('/configuracion/actualizar', function (Request $request) {
            $user = Auth::user();

            $data = $request->validate([
                'nombre' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'dni' => [
                    'required',
                    'string',
                    'size:9',
                    'regex:/^[0-9]{8}[A-Za-z]$/',
                    function ($attribute, $value, $fail) {
                        $dni = strtoupper((string) $value);
                        $numero = (int) substr($dni, 0, 8);
                        $letra = substr($dni, 8, 1);
                        $letrasValidas = 'TRWAGMYFPDXBNJZSQVHLCKE';
                        $letraCorrecta = $letrasValidas[$numero % 23];

                        if ($letra !== $letraCorrecta) {
                            $fail('El DNI no es valido (letra incorrecta).');
                        }
                    },
                ],
                'telefono' => ['required', 'regex:/^[6789]\d{8}$/'],
                'domicilio' => 'required|string|max:255',
            ], [
                'dni.regex' => 'El DNI debe tener 8 numeros y 1 letra (ej: 12345678Z).',
                'telefono.regex' => 'El telefono debe tener 9 digitos y empezar por 6, 7, 8 o 9.',
            ]);

            $data['dni'] = strtoupper($data['dni']);

            $user->update($data);

            return back()->with('success', 'Tus datos se han actualizado correctamente.');
        })->name('configuracion.actualizar');
    });

    Route::post('/perfil/password', [PasswordController::class, 'cambiarPasswordPerfil'])->name('perfil.password');

    Route::post('/pago/cambiar-plan-metodo', [PagoController::class, 'cambiarPlanMetodo'])
        ->name('pago.cambiar_plan_metodo');


});


/*
|--------------------------------------------------------------------------
| Recuperacion de contraseña
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
    return back()->with('success', 'Candidatura enviada con exito.');
})->name('empleo.enviar');

/*
|--------------------------------------------------------------------------
| Zona admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    Route::get('/usuarios/nuevo', [AdminController::class, 'create'])->name('admin.user.create');
    Route::post('/usuarios', [AdminController::class, 'store'])->name('admin.user.store');
    Route::get('/usuario/editar/{user}', [AdminController::class, 'edit'])->name('admin.user.edit');
    Route::put('/usuario/actualizar/{user}', [AdminController::class, 'update'])->name('admin.user.update');
    Route::delete('/usuario/eliminar/{user}', [AdminController::class, 'destroy'])->name('admin.user.delete');

    Route::put('/usuario/{user}/plan', [AdminController::class, 'changePlan'])->name('admin.user.plan');
    Route::post('/usuario/{user}/cobro-manual', [AdminController::class, 'manualCharge'])->name('admin.user.manual_charge');
    Route::post('/usuario/{user}/renovar', [AdminController::class, 'renewSubscription'])->name('admin.user.renew');
    Route::post('/usuario/{user}/impago', [AdminController::class, 'markUnpaid'])->name('admin.user.mark_unpaid');

    Route::get('/clases', [AdminController::class, 'clasesIndex'])->name('admin.clases.index');
    Route::post('/clases', [AdminController::class, 'claseStore'])->name('admin.clases.store');
    Route::put('/clases/{clase}', [AdminController::class, 'claseUpdate'])->name('admin.clases.update');
    Route::delete('/clases/{clase}', [AdminController::class, 'claseDestroy'])->name('admin.clases.destroy');

    Route::post('/clases/{clase}/usuarios', [AdminController::class, 'anadirUsuarioClase'])->name('admin.clases.usuarios.store');
    Route::delete('/clases/{clase}/usuarios/{user}', [AdminController::class, 'quitarUsuarioClase'])->name('admin.clases.usuarios.destroy');
    Route::post('/usuario/{user}/aprobar-manual', [AdminController::class, 'aprobarPagoManual'])
        ->name('admin.user.aprobar_manual');

});

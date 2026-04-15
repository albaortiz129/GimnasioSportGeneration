<?php

/**
 * Definicion de rutas web de SeaFit: paginas publicas, privadas y administracion.
 */
use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AdminDiscountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| Paginas publicas
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('home.index'))->name('home');
Route::get('/tarifas', fn() => view('pricing.index'))->name('tarifas');

Route::view('/faq', 'support.faq')->name('faq');
Route::view('/contacto', 'support.contact')->name('contacto');
Route::view('/sobre-nosotros', 'support.about')->name('nosotros');
Route::view('/trabaja-con-nosotros', 'support.jobs')->name('empleo');

Route::get('/servicios', [ServiceController::class, 'index'])->name('servicios');
Route::get('/agenda', [ServiceController::class, 'agenda'])->name('agenda');

Route::get('/valoracion', fn() => view('services.assessment'))->name('valoracion');
Route::post('/valoracion', [AssessmentController::class, 'send'])->name('valoracion.enviar');

/*
|--------------------------------------------------------------------------
| Autenticacion
|--------------------------------------------------------------------------
*/
Route::get('/registro', fn() => view('user.register'))->name('registro');
Route::get('/login', fn() => view('user.login'))->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Zona privada de socio
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Cambio obligatorio de contraseña temporal al primer inicio.
    Route::get('/cambiar-password-inicial', [PasswordController::class, 'showInitialChangeForm'])
        ->name('password.force.form');
    Route::post('/cambiar-password-inicial', [PasswordController::class, 'changeInitialPassword'])
        ->name('password.force.update');

    Route::middleware(['member', 'force.password'])->group(function () {
        // Perfil privado del socio.
        Route::get('/perfil', function () {
            $user = Auth::user()->load('classes');
            return view('user.profile', compact('user'));
        })->name('perfil');


        // Historial de reservas del socio.
        Route::get('/mis-reservas', function () {
            $user = Auth::user()->load('classes');
            return view('user.my-bookings', compact('user'));
        })->name('mis.reservas');

        Route::post('/reservar/{id}', [BookingController::class, 'book'])->name('clase.reservar');
        Route::delete('/reservar/{id}', [BookingController::class, 'cancel'])->name('clase.cancelar');

        Route::get('/gestion-pago', [PaymentController::class, 'index'])->name('pago.gestion');
        Route::post('/plan/cancelar', [PaymentController::class, 'cancelPlan'])->name('plan.cancelar');
        Route::post('/plan/reanudar', [PaymentController::class, 'resumePlan'])->name('plan.reanudar');
        Route::get('/factura/descargar/{id}', [PaymentController::class, 'downloadInvoice'])->name('factura.descargar');

        Route::get('/pago/nuevo', [PaymentController::class, 'newMethod'])->name('pago.nuevo');
        Route::post('/pago/principal', [PaymentController::class, 'setPrimaryMethod'])->name('pago.principal');
        Route::delete('/pago/eliminar', [PaymentController::class, 'deleteMethod'])->name('pago.eliminar');
        Route::post('/pago/guardar', [PaymentController::class, 'saveMethod'])->name('pago.guardar');
        Route::post('/pago/guardar-manual', [PaymentController::class, 'saveManualMethod'])->name('pago.guardar_manual');
        Route::post('/pago/principal-manual', [PaymentController::class, 'setPrimaryManualMethod'])->name('pago.principal_manual');
        Route::delete('/pago/eliminar-manual', [PaymentController::class, 'deleteManualMethod'])->name('pago.eliminar_manual');

        Route::get('/configuracion', function () {
            $user = Auth::user();
            return view('user.settings', compact('user'));
        })->name('configuracion');

        Route::post('/configuracion/actualizar', function (Request $request) {
            $user = Auth::user();

            // Mismas reglas de validacion que en registro/admin.
            $data = $request->validate([
                'nombre' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'dni' => [
                    'required',
                    'string',
                    'size:9',
                    'regex:/^[0-9]{8}[A-Za-z]$/',
                    Rule::unique('users', 'dni')->ignore($user->id),
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

            // Guarda cambios de la ficha de socio.
            $user->update($data);

            return back()->with('success', 'Tus datos se han actualizado correctamente.');
        })->name('configuracion.actualizar');
    });

    Route::post('/perfil/password', [PasswordController::class, 'changeProfilePassword'])->name('perfil.password');

    Route::post('/pago/cambiar-plan-metodo', [PaymentController::class, 'changePlanMethod'])
        ->name('pago.cambiar_plan_metodo');
});

/*
|--------------------------------------------------------------------------
| Recuperacion de contraseña
|--------------------------------------------------------------------------
*/
Route::get('/recuperar-password', [PasswordController::class, 'showRequestForm'])->name('password.request');
Route::post('/recuperar-password', [PasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordController::class, 'updatePassword'])->name('password.update');

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
    Route::get('/dashboard', [AdminPanelController::class, 'index'])->name('admin.dashboard');

    Route::get('/usuarios/nuevo', [AdminPanelController::class, 'create'])->name('admin.user.create');
    Route::post('/usuarios', [AdminPanelController::class, 'store'])->name('admin.user.store');
    Route::get('/usuario/editar/{user}', [AdminPanelController::class, 'edit'])->name('admin.user.edit');
    Route::put('/usuario/actualizar/{user}', [AdminPanelController::class, 'update'])->name('admin.user.update');
    Route::delete('/usuario/eliminar/{user}', [AdminPanelController::class, 'destroy'])->name('admin.user.delete');

    Route::put('/usuario/{user}/plan', [AdminPanelController::class, 'changePlan'])->name('admin.user.plan');
    Route::post('/usuario/{user}/cobro-manual', [AdminPanelController::class, 'manualCharge'])->name('admin.user.manual_charge');
    Route::post('/usuario/{user}/renovar', [AdminPanelController::class, 'renewSubscription'])->name('admin.user.renew');
    Route::post('/usuario/{user}/impago', [AdminPanelController::class, 'markUnpaid'])->name('admin.user.mark_unpaid');

    Route::get('/clases', [AdminPanelController::class, 'classesIndex'])->name('admin.classes.index');
    Route::post('/clases', [AdminPanelController::class, 'classStore'])->name('admin.classes.store');
    Route::put('/clases/{clase}', [AdminPanelController::class, 'classUpdate'])->name('admin.classes.update');
    Route::delete('/clases/{clase}', [AdminPanelController::class, 'classDestroy'])->name('admin.classes.destroy');

    Route::post('/clases/{clase}/usuarios', [AdminPanelController::class, 'addUserToClass'])->name('admin.classes.usuarios.store');
    Route::delete('/clases/{clase}/usuarios/{user}', [AdminPanelController::class, 'removeUserFromClass'])->name('admin.classes.usuarios.destroy');
    Route::post('/usuario/{user}/aprobar-manual', [AdminPanelController::class, 'approveManualPayment'])
        ->name('admin.user.aprobar_manual');

    Route::get('/descuentos', [AdminDiscountController::class, 'index'])->name('admin.discounts.index');
    Route::get('/descuentos/nuevo', [AdminDiscountController::class, 'create'])->name('admin.discounts.create');
    Route::post('/descuentos', [AdminDiscountController::class, 'store'])->name('admin.discounts.store');
    Route::get('/descuentos/{discountCode}/editar', [AdminDiscountController::class, 'edit'])->name('admin.discounts.edit');
    Route::put('/descuentos/{discountCode}', [AdminDiscountController::class, 'update'])->name('admin.discounts.update');
    Route::delete('/descuentos/{discountCode}', [AdminDiscountController::class, 'destroy'])->name('admin.discounts.destroy');
});

<?php

/**
 * Rutas web principales de Sport Generation.
 */

use App\Http\Controllers\AdminDiscountController;
use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

/*
--------------------------------------------------------------------------
 PÁGINAS PÚBLICAS (Rutas sin iniciar sesión)
--------------------------------------------------------------------------
*/

// Página principal.
Route::get('/', fn() => view('home.index'))->name('home');

// Página de tarifas.
Route::get('/tarifas', fn() => view('pricing.index'))->name('tarifas');

// Páginas del footer.
Route::view('/faq', 'support.faq')->name('faq');
Route::view('/contacto', 'support.contact')->name('contacto');
Route::view('/sobre-nosotros', 'support.about')->name('nosotros');

// Páginas de servicios y agenda.
Route::get('/servicios', [ServiceController::class, 'index'])->name('servicios');
Route::get('/agenda', [ServiceController::class, 'agenda'])->name('agenda');

// Formulario de valoración.
Route::get('/valoracion', fn() => view('services.assessment'))->name('valoracion');
Route::post('/valoracion', [AssessmentController::class, 'send'])->name('valoracion.enviar');

/*
--------------------------------------------------------------------------
 AUTENTICACIÓN (Registro, login y cierre de sesión)
--------------------------------------------------------------------------
*/

// Vista de registro.
Route::get('/registro', fn() => view('user.register'))->name('registro');

// Vista de login.
Route::get('/login', fn() => view('user.login'))->name('login');

// Envío del formulario de login.
Route::post('/login', [AuthController::class, 'login']);

// Cierre de sesión.
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
--------------------------------------------------------------------------
 RECUPERACIÓN DE CONTRASEÑA
--------------------------------------------------------------------------
*/

// Muestra formulario para pedir enlace de recuperación.
Route::get('/recuperar-password', [PasswordController::class, 'showRequestForm'])->name('password.request');

// Envía el correo con el enlace de recuperación.
Route::post('/recuperar-password', [PasswordController::class, 'sendResetLink'])->name('password.email');

// Muestra formulario para escribir nueva contraseña.
Route::get('/reset-password/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');

// Guarda la nueva contraseña.
Route::post('/reset-password', [PasswordController::class, 'updatePassword'])->name('password.update');

/*
--------------------------------------------------------------------------
 RUTAS DE USUARIO AUTENTICADO (Requieren sesión iniciada)
--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Pantallas para cambio obligatorio de contraseña temporal.
    Route::get('/cambiar-password-inicial', [PasswordController::class, 'showInitialChangeForm'])->name('password.force.form');
    Route::post('/cambiar-password-inicial', [PasswordController::class, 'changeInitialPassword'])->name('password.force.update');
});

Route::middleware(['auth', 'member', 'force.password'])->group(function () {
    // PERFIL: carga usuario y sus clases.
    Route::get('/perfil', function () {
        $user = Auth::user();
        $user->load('classes');

        return view('user.profile', compact('user'));
    })->name('perfil');

    // MIS RESERVAS: lista reservas del usuario.
    Route::get('/mis-reservas', function () {
        $user = Auth::user();
        $user->load('classes');

        return view('user.my-bookings', compact('user'));
    })->name('mis.reservas');

    // BLOQUE DE PAGOS: ver pago, cambiar plan, tarjeta, métodos y facturas.
    Route::get('/mi-pago', [PaymentController::class, 'index'])->name('pago.gestion');
    Route::post('/mi-pago/plan/cancelar', [PaymentController::class, 'cancelPlan'])->name('plan.cancelar');
    Route::post('/mi-pago/plan/reanudar', [PaymentController::class, 'resumePlan'])->name('plan.reanudar');

    Route::post('/mi-pago/principal', [PaymentController::class, 'setPrimaryMethod'])->name('pago.principal');
    Route::delete('/mi-pago/eliminar', [PaymentController::class, 'deleteMethod'])->name('pago.eliminar');
    Route::get('/mi-pago/factura/{invoiceId}', [PaymentController::class, 'downloadInvoice'])->name('factura.descargar');

    Route::get('/mi-pago/nueva-tarjeta', [PaymentController::class, 'newMethod'])->name('pago.nuevo');
    Route::post('/mi-pago/guardar-tarjeta', [PaymentController::class, 'saveMethod'])->name('pago.guardar');

    Route::post('/mi-pago/guardar-manual', [PaymentController::class, 'saveManualMethod'])->name('pago.guardar_manual');
    Route::post('/mi-pago/principal-manual', [PaymentController::class, 'setPrimaryManualMethod'])->name('pago.principal_manual');
    Route::delete('/mi-pago/eliminar-manual', [PaymentController::class, 'deleteManualMethod'])->name('pago.eliminar_manual');

    Route::post('/mi-pago/cambiar-plan-metodo', [PaymentController::class, 'changePlanMethod'])->name('pago.cambiar_plan_metodo');

    // CONFIGURACIÓN: muestra y guarda datos de cuenta.
    Route::get('/configuracion', function () {
        $user = Auth::user();

        return view('user.settings', compact('user'));
    })->name('configuracion');

    Route::post('/configuracion/actualizar', function (Request $request) {
        $user = Auth::user();

        // Validación de datos editables del perfil.
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'dni' => ['required', 'string', 'size:9', Rule::unique('users', 'dni')->ignore($user->id)],
            'fecha_nacimiento' => 'required|date',
            'telefono' => ['required', 'regex:/^[6789]\d{8}$/'],
            'domicilio' => 'required|string|max:255',
        ], [
            'dni.size' => 'El DNI debe tener 9 caracteres.',
            'telefono.regex' => 'El teléfono debe tener 9 dígitos y empezar por 6, 7, 8 o 9.',
        ]);

        // Normalización previa antes de actualizar.
        $data['nombre'] = trim((string) $data['nombre']);
        $data['apellidos'] = trim((string) $data['apellidos']);
        $data['email'] = strtolower(trim((string) $data['email']));
        $data['dni'] = strtoupper(trim((string) $data['dni']));
        $data['telefono'] = trim((string) $data['telefono']);
        $data['domicilio'] = trim((string) $data['domicilio']);

        $user->update($data);

        return back()->with('success', 'Datos actualizados correctamente.');
    })->name('configuracion.actualizar');

    // Cambio de contraseña desde perfil.
    Route::post('/perfil/password', [PasswordController::class, 'changeProfilePassword'])->name('perfil.password');

    // Reservas de clases del socio.
    Route::post('/clase/{id}/reservar', [BookingController::class, 'book'])->name('clase.reservar');
    Route::delete('/clase/{id}/cancelar', [BookingController::class, 'cancel'])->name('clase.cancelar');
});

/*
--------------------------------------------------------------------------
 ZONA DE ADMINISTRADORES (Requiere auth + middleware admin.)
--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Dashboard principal.
    Route::get('/dashboard', [AdminPanelController::class, 'index'])->name('admin.dashboard');

    // CRUD de usuarios.
    Route::get('/usuarios/nuevo', [AdminPanelController::class, 'create'])->name('admin.user.create');
    Route::post('/usuarios', [AdminPanelController::class, 'store'])->name('admin.user.store');
    Route::get('/usuario/editar/{user}', [AdminPanelController::class, 'edit'])->name('admin.user.edit');
    Route::put('/usuario/actualizar/{user}', [AdminPanelController::class, 'update'])->name('admin.user.update');
    Route::delete('/usuario/eliminar/{user}', [AdminPanelController::class, 'destroy'])->name('admin.user.delete');

    // Acciones de cobro y estado de pago.
    Route::put('/usuario/{user}/plan', [AdminPanelController::class, 'changePlan'])->name('admin.user.plan');
    Route::post('/usuario/{user}/cobro-manual', [AdminPanelController::class, 'manualCharge'])->name('admin.user.manual_charge');
    Route::post('/usuario/{user}/renovar', [AdminPanelController::class, 'renewSubscription'])->name('admin.user.renew');
    Route::post('/usuario/{user}/impago', [AdminPanelController::class, 'markUnpaid'])->name('admin.user.mark_unpaid');
    Route::post('/usuario/{user}/aprobar-manual', [AdminPanelController::class, 'approveManualPayment'])->name('admin.user.aprobar_manual');

    // Gestión de clases y alumnos inscritos.
    Route::get('/clases', [AdminPanelController::class, 'classesIndex'])->name('admin.classes.index');
    Route::post('/clases', [AdminPanelController::class, 'classStore'])->name('admin.classes.store');
    Route::put('/clases/{clase}', [AdminPanelController::class, 'classUpdate'])->name('admin.classes.update');
    Route::delete('/clases/{clase}', [AdminPanelController::class, 'classDestroy'])->name('admin.classes.destroy');

    Route::post('/clases/{clase}/usuarios', [AdminPanelController::class, 'addUserToClass'])->name('admin.classes.usuarios.store');
    Route::delete('/clases/{clase}/usuarios/{user}', [AdminPanelController::class, 'removeUserFromClass'])->name('admin.classes.usuarios.destroy');

    // CRUD de códigos de descuento.
    Route::get('/descuentos', [AdminDiscountController::class, 'index'])->name('admin.discounts.index');
    Route::get('/descuentos/nuevo', [AdminDiscountController::class, 'create'])->name('admin.discounts.create');
    Route::post('/descuentos', [AdminDiscountController::class, 'store'])->name('admin.discounts.store');
    Route::get('/descuentos/{discountCode}/editar', [AdminDiscountController::class, 'edit'])->name('admin.discounts.edit');
    Route::put('/descuentos/{discountCode}', [AdminDiscountController::class, 'update'])->name('admin.discounts.update');
    Route::delete('/descuentos/{discountCode}', [AdminDiscountController::class, 'destroy'])->name('admin.discounts.destroy');
});

/*
--------------------------------------------------------------------------
 CHAT IA (Endpoint que recibe la pregunta y responde en JSON.)
--------------------------------------------------------------------------
*/
Route::post('/ia/chat', [AiChatController::class, 'ask'])->name('ia.chat');

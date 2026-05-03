<?php

/**
 * Rutas web principales de SeaFit.
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

/*
--------------------------------------------------------------------------
 PAGINAS PUBLICAS (Rutas sin iniciar sesión)
--------------------------------------------------------------------------
*/

// Pagina principal.
Route::get('/', fn() => view('home.index'))->name('home');

// Pagina de tarifas.
Route::get('/tarifas', fn() => view('pricing.index'))->name('tarifas');

// Paginas del footer.
Route::view('/faq', 'support.faq')->name('faq');
Route::view('/contacto', 'support.contact')->name('contacto');
Route::view('/sobre-nosotros', 'support.about')->name('nosotros');
Route::view('/trabaja-con-nosotros', 'support.jobs')->name('empleo');

// Paginas de servicios y agenda.
Route::get('/servicios', [ServiceController::class, 'index'])->name('servicios');
Route::get('/agenda', [ServiceController::class, 'agenda'])->name('agenda');

// Formulario de valoración.
Route::get('/valoracion', fn() => view('services.assessment'))->name('valoracion');
Route::post('/valoracion', [AssessmentController::class, 'send'])->name('valoracion.enviar');

/*
--------------------------------------------------------------------------
 AUTENTICACION (Registro, login y cierre de sesion.)
--------------------------------------------------------------------------
*/

// Vista de registro.
Route::get('/registro', fn() => view('user.register'))->name('registro');

// Vista de login.
Route::get('/login', fn() => view('user.login'))->name('login');

// Envio del formulario de login.
Route::post('/login', [AuthController::class, 'login']);

// Cierre de sesion.
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
--------------------------------------------------------------------------
 RECUPERACIÓN DE CONTRASEÑA
--------------------------------------------------------------------------
*/

// Muestra formulario para pedir enlace de recuperacion.
Route::get('/recuperar-password', [PasswordController::class, 'showRequestForm'])->name('password.request');

// Envia el correo con el enlace de recuperacion.
Route::post('/recuperar-password', [PasswordController::class, 'sendResetLink'])->name('password.email');

// Muestra formulario para escribir nueva contrasena.
Route::get('/reset-password/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');

// Guarda la nueva contrasena.
Route::post('/reset-password', [PasswordController::class, 'updatePassword'])->name('password.update');

/*
--------------------------------------------------------------------------
 TRABAJA CON NOSOTROS
--------------------------------------------------------------------------
*/

Route::post('/trabaja-con-nosotros/enviar', function (Request $request) {
    // Validacion de campos del formulario.
    $data = $request->validate([
        'nombre' => 'required|string|max:120',
        'email' => 'required|email|max:190',
        'telefono' => 'nullable|string|max:30',
        'puesto' => 'required|string|max:120',
        'mensaje' => 'nullable|string|max:2000',
        'cv' => 'required|file|mimes:pdf|max:5120', // max:5120 = 5MB.
    ], [
        'nombre.required' => 'El nombre es obligatorio.',
        'email.required' => 'El correo es obligatorio.',
        'email.email' => 'El correo no tiene un formato valido.',
        'puesto.required' => 'Indica el puesto al que te presentas.',
        'cv.required' => 'Debes adjuntar tu CV en PDF.',
        'cv.mimes' => 'El CV debe ser un archivo PDF.',
        'cv.max' => 'El CV no puede superar 5 MB.',
    ]);

    // Normalizacion de datos para guardar/mostrar de forma consistente.
    $data['nombre'] = trim((string) $data['nombre']);
    $data['email'] = strtolower(trim((string) $data['email']));
    $data['telefono'] = trim((string) ($data['telefono'] ?? ''));
    $data['puesto'] = trim((string) $data['puesto']);
    $data['mensaje'] = trim((string) ($data['mensaje'] ?? ''));

    try {
        // Envio del correo con plantilla y CV adjunto.
        Mail::send('emails.job-application', ['data' => $data], function ($message) use ($data, $request) {
            $message->to('soporte.seafit@gmail.com');
            $message->replyTo($data['email'], $data['nombre']);
            $message->subject('Nueva candidatura - Trabaja con nosotros (SeaFit)');

            // Adjunta el archivo solo si llega correctamente.
            if ($request->hasFile('cv')) {
                $cv = $request->file('cv');
                $message->attach(
                    $cv->getRealPath(),
                    [
                        'as' => $cv->getClientOriginalName(),
                        'mime' => $cv->getMimeType(),
                    ]
                );
            }
        });
    } catch (\Throwable $e) {
        // Si falla el correo, se registra el error y se vuelve al formulario.
        Log::error('Error al enviar candidatura de trabajo.', [
            'email' => $data['email'],
            'error' => $e->getMessage(),
        ]);

        return back()
            ->withInput()
            ->withErrors(['formulario' => 'No se pudo enviar la candidatura ahora mismo. Intentalo de nuevo en unos minutos.']);
    }

    // Confirmacion final en pantalla.
    return back()->with('success', 'Candidatura enviada correctamente. Te contactaremos si encaja con el perfil.');
})->name('empleo.enviar');

/*
--------------------------------------------------------------------------
 RUTAS DE USUARIO AUTENTICADO (Requieren sesion iniciada.)
--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Pantallas para cambio obligatorio de contrasena temporal.
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

    // BLOQUE DE PAGOS: ver pago, cambiar plan, tarjeta, metodos y facturas.
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

    // CONFIGURACION: muestra y guarda datos de cuenta.
    Route::get('/configuracion', function () {
        $user = Auth::user();

        return view('user.settings', compact('user'));
    })->name('configuracion');

    Route::post('/configuracion/actualizar', function (Request $request) {
        $user = Auth::user();

        // Validacion de datos editables del perfil.
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
            'telefono.regex' => 'El telefono debe tener 9 digitos y empezar por 6, 7, 8 o 9.',
        ]);

        // Normalizacion previa antes de actualizar.
        $data['nombre'] = trim((string) $data['nombre']);
        $data['apellidos'] = trim((string) $data['apellidos']);
        $data['email'] = strtolower(trim((string) $data['email']));
        $data['dni'] = strtoupper(trim((string) $data['dni']));
        $data['telefono'] = trim((string) $data['telefono']);
        $data['domicilio'] = trim((string) $data['domicilio']);

        $user->update($data);

        return back()->with('success', 'Datos actualizados correctamente.');
    })->name('configuracion.actualizar');

    // Cambio de contrasena desde perfil.
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

    // Gestion de clases y alumnos inscritos.
    Route::get('/clases', [AdminPanelController::class, 'classesIndex'])->name('admin.classes.index');
    Route::post('/clases', [AdminPanelController::class, 'classStore'])->name('admin.classes.store');
    Route::put('/clases/{clase}', [AdminPanelController::class, 'classUpdate'])->name('admin.classes.update');
    Route::delete('/clases/{clase}', [AdminPanelController::class, 'classDestroy'])->name('admin.classes.destroy');

    Route::post('/clases/{clase}/usuarios', [AdminPanelController::class, 'addUserToClass'])->name('admin.classes.usuarios.store');
    Route::delete('/clases/{clase}/usuarios/{user}', [AdminPanelController::class, 'removeUserFromClass'])->name('admin.classes.usuarios.destroy');

    // CRUD de codigos de descuento.
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

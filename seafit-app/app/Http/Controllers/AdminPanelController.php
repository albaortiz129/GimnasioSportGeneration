<?php

/**
 * Controlador del panel de administración.
 * Gestiona usuarios, cobros manuales y clases.
 */
namespace App\Http\Controllers;

use App\Models\GymClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminPanelController extends Controller
{
    /**
     * Muestra el dashboard con usuarios e impagados.
     */
    public function index(Request $request)
    {
        // Texto del buscador del panel.
        $buscar = trim((string) $request->query('q', ''));

        $discountsTablesReady = Schema::hasTable('discount_redemptions')
            && Schema::hasTable('discount_codes');

        $usuariosQuery = User::query()
            ->where('is_admin', false)
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($sub) use ($buscar) {
                    $sub->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('apellidos', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('dni', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombre')
            ->orderBy('apellidos');

        if ($discountsTablesReady) {
            $usuariosQuery->with(['latestDiscountRedemption.discountCode']);
        }

        try {
            $usuarios = $usuariosQuery->get();
        } catch (QueryException $exception) {
            // Si faltan tablas de descuentos, no rompemos el dashboard.
            report($exception);
            $discountsTablesReady = false;

            $usuarios = User::query()
                ->where('is_admin', false)
                ->when($buscar !== '', function ($query) use ($buscar) {
                    $query->where(function ($sub) use ($buscar) {
                        $sub->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('apellidos', 'like', "%{$buscar}%")
                            ->orWhere('email', 'like', "%{$buscar}%")
                            ->orWhere('dni', 'like', "%{$buscar}%");
                    });
                })
                ->orderBy('nombre')
                ->orderBy('apellidos')
                ->get();
        }

        $impagados = collect();
        $billingColumnsReady = Schema::hasColumn('users', 'payment_status')
            && Schema::hasColumn('users', 'next_payment_at');

        if ($billingColumnsReady) {
            try {
                // Consulta de clientes con impago o con fecha de cobro vencida.
                $impagadosQuery = User::query()
                    ->where('is_admin', false)
                    ->where(function ($query) {
                        $query->where('payment_status', 'impagado')
                            ->orWhere('payment_status', 'pendiente')
                            ->orWhere(function ($sub) {
                                $sub->where('payment_status', '!=', 'al_dia')
                                    ->whereNotNull('next_payment_at')
                                    ->whereDate('next_payment_at', '<', today());
                            });
                    })
                    ->orderByRaw("FIELD(payment_status, 'impagado', 'pendiente', 'al_dia')")
                    ->orderBy('next_payment_at');

                if ($discountsTablesReady) {
                    $impagadosQuery->with(['latestDiscountRedemption.discountCode']);
                }

                $impagados = $impagadosQuery->get();

            } catch (QueryException $exception) {
                // Si la base de datos no está al día, no rompemos el panel.
                report($exception);
                $billingColumnsReady = false;
                $impagados = collect();
            }
        }

        return view('admin.dashboard', compact(
            'usuarios',
            'impagados',
            'buscar',
            'billingColumnsReady',
            'discountsTablesReady'
        ));
    }

    /**
     * Muesta el formulario para crear usuario desde admin sin guardar en la BBDD.
     */
    public function create(Request $request)
    {
        // Renueva el token para evitar 419 en formularios largos.
        $request->session()->regenerateToken();

        return view('admin.create-user');
    }

    /**
     * Crea un nuevo socio desde admin.
     */
    public function store(Request $request)
    {
        // Validacion completa del formulario de alta.
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => [
                'required',
                'string',
                'size:9',
                'regex:/^[0-9]{8}[A-Za-z]$/',
                'unique:users,dni',
                function ($attribute, $value, $fail) {
                    $dni = strtoupper((string) $value);
                    $numero = (int) substr($dni, 0, 8);
                    $letra = substr($dni, 8, 1);
                    $letrasValidas = 'TRWAGMYFPDXBNJZSQVHLCKE';
                    $letraCorrecta = $letrasValidas[$numero % 23];

                    if ($letra !== $letraCorrecta) {
                        $fail('El DNI no es válido (letra incorrecta).');
                    }
                },
            ],
            'fecha_nacimiento' => 'required|date',
            'telefono' => ['required', 'regex:/^[6789]\d{8}$/'],
            'email' => 'required|email|unique:users,email',
            'domicilio' => 'required|string|max:255',
            'tarifa' => 'required|in:mensual,trimestral,anual',
            'metodo_pago' => 'required|string|max:50',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
        ], [
            'telefono.regex' => 'El teléfono debe tener 9 dígitos y empezar por 6, 7, 8 o 9.',
            'dni.regex' => 'El DNI debe tener 8 números y 1 letra (ej: 12345678Z).',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
        ]);

        $data['dni'] = strtoupper($data['dni']);

        $user = User::create([
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'dni' => $data['dni'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'telefono' => $data['telefono'],
            'email' => $data['email'],
            'domicilio' => $data['domicilio'],
            'tarifa' => $data['tarifa'],
            'metodo_pago' => $data['metodo_pago'],
            'password' => Hash::make($data['password']),
            'must_change_password' => true,
            'payment_status' => 'pendiente',
            'is_admin' => false,
        ]);

        $welcomeEmailSent = $this->sendWelcomeEmail($user);

        $mensajeCreacion = 'Usuario creado correctamente.';
        if (!$welcomeEmailSent) {
            $mensajeCreacion .= ' No se pudo enviar el correo de bienvenida en este momento.';
        }

        return redirect()->route('admin.user.edit', $user)
            ->with('success', $mensajeCreacion);
    }

    /**
     * Envía email de bienvenida al nuevo socio.
     */
    private function sendWelcomeEmail(User $user): bool
    {
        try {
            Mail::send('emails.bienvenida', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Bienvenido a SeaFit');
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de bienvenida desde admin.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Formulario de edición de usuario.
     */
    public function edit(User $user)
    {
        return view('admin.edit-user', compact('user'));
    }

    /**
     * Actualiza datos de un usuario desde admin.
     */
    public function update(Request $request, User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No se editan datos de socio para administradores.');
        }

        // Campos editables del cliente desde el panel.
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => ['required', 'string', Rule::unique('users', 'dni')->ignore($user->id)],
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|string|max:20',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'domicilio' => 'required|string|max:255',
            'tarifa' => 'required|in:mensual,trimestral,anual,cancelada',
            'metodo_pago' => 'required|string|max:50',
            'payment_status' => 'required|in:al_dia,pendiente,impagado',
            'next_payment_at' => 'nullable|date',
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'Debe incluir mayúscula, minúscula, número y carácter especial.',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
            $data['must_change_password'] = true; // opcional: forzar cambio al entrar
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.dashboard')->with('success', 'Usuario actualizado.');
    }

    /**
     * Cambia la tarifa de un usuario.
     */
    public function changePlan(Request $request, User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No aplica a administradores.');
        }

        $data = $request->validate([
            'tarifa' => 'required|in:mensual,trimestral,anual,cancelada',
        ]);

        // Calcula la siguiente fecha solo cuando hay plan activo.
        $nextPayment = $data['tarifa'] === 'cancelada'
            ? null
            : $this->nextChargeDate($data['tarifa']);

        $user->update([
            'tarifa' => $data['tarifa'],
            'payment_status' => $data['tarifa'] === 'cancelada' ? 'pendiente' : 'al_dia',
            'next_payment_at' => $nextPayment,
        ]);

        return back()->with('success', 'Plan actualizado.');
    }

    /**
     * Registra un cobro manual y activa el pago al día.
     */
    public function manualCharge(Request $request, User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No aplica a administradores.');
        }

        $data = $request->validate([
            'tarifa' => 'required|in:mensual,trimestral,anual',
            'metodo_manual' => 'required|in:efectivo,bizum,transferencia,tarjeta,paypal',
            'nota' => 'nullable|string|max:255',
        ]);

        $user->update([
            'tarifa' => $data['tarifa'],
            'metodo_pago' => $data['metodo_manual'],
            'payment_status' => 'al_dia',
            'last_manual_payment_at' => now(),
            'next_payment_at' => $this->nextChargeDate($data['tarifa']),
            'manual_payment_note' => $data['nota'],
        ]);

        return back()->with('success', 'Cobro manual registrado y pago al día.');
    }

    /**
     * Renueva manualmente la suscripción.
     */
    public function renewSubscription(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No aplica a administradores.');
        }

        // Si ya existe una fecha futura, no se acumulan meses al pulsar varias veces.
        if ($user->next_payment_at) {
            $fechaActual = Carbon::parse($user->next_payment_at);

            if ($fechaActual->isToday() || $fechaActual->isFuture()) {
                $user->update([
                    'payment_status' => 'al_dia',
                    'next_payment_at' => $fechaActual->toDateString(),
                ]);

                return back()->with('success', 'Pago regularizado. La fecha de renovación se mantiene.');
            }
        }

        $user->update([
            'payment_status' => 'al_dia',
            'next_payment_at' => $this->nextChargeDate($user->tarifa, now()),
        ]);

        return back()->with('success', 'Suscripción renovada.');
    }

    /**
     * Marca un usuario como impagado.
     */
    public function markUnpaid(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No aplica a administradores.');
        }

        $user->update([
            'payment_status' => 'impagado',
        ]);

        return back()->with('success', 'Cliente marcado como impagado.');
    }

    /**
     * Elimina un usuario socio.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        if ($user->is_admin) {
            return back()->with('error', 'No puedes eliminar otro administrador desde aquí.');
        }

        $user->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Usuario eliminado.');
    }

    /**
     * Pantalla de administración de clases con filtros.
     */
    public function classesIndex(Request $request)
    {
        $dia = $request->query('dia');

        $diasSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

        $ordenDias = "FIELD(dia_semana, 'Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo')";

        // Carga clases con usuarios asociados (solo socios, sin admins).
        $clases = GymClass::with([
            'users' => function ($query) {
                $query->where('is_admin', false)
                    ->orderBy('nombre')
                    ->orderBy('apellidos');
            }
        ])
            ->when($dia, fn($query) => $query->whereIn('dia_semana', $this->weekdayVariants($dia)))
            ->orderByRaw($ordenDias)
            ->orderBy('hora_inicio')
            ->get();

        $usuarios = User::where('is_admin', false)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get();

        return view('admin.classes', compact('clases', 'usuarios', 'dia', 'diasSemana'));
    }

    /**
     * Crea una nueva clase desde admin.
     */
    public function classStore(Request $request)
    {
        // Reglas basicas de alta para evitar datos incompletos.
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'instructor' => 'required|string|max:255',
            'sala' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'dia_semana' => 'required|in:Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo',
            'capacidad_max' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|string|max:255',
        ]);

        $data['dia_semana'] = $this->normalizeWeekday($data['dia_semana']);

        GymClass::create($data);

        return back()->with('success', 'Clase creada.');
    }

    /**
     * Actualiza una clase existente.
     */
    public function classUpdate(Request $request, GymClass $clase)
    {
        // Se aplican las mismas reglas que en el alta.
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'instructor' => 'required|string|max:255',
            'sala' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
            'dia_semana' => 'required|in:Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo',
            'capacidad_max' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|string|max:255',
        ]);

        $data['dia_semana'] = $this->normalizeWeekday($data['dia_semana']);

        $clase->update($data);

        return back()->with('success', 'Clase actualizada.');
    }

    /**
     * Elimina una clase del calendario.
     */
    public function classDestroy(GymClass $clase)
    {
        $clase->delete();

        return back()->with('success', 'Clase eliminada.');
    }

    /**
     * Anade un usuario a una clase y descuenta una plaza.
     */
    public function addUserToClass(Request $request, GymClass $clase)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::where('is_admin', false)->findOrFail($request->user_id);

        $resultado = DB::transaction(function () use ($clase, $user) {
            // Bloqueo de la clase para evitar sobrecupo por concurrencia.
            $claseBloqueada = GymClass::query()->lockForUpdate()->findOrFail($clase->id);

            if ($claseBloqueada->users()->where('user_id', $user->id)->exists()) {
                return 'duplicate';
            }

            if ($claseBloqueada->capacidad_max <= 0) {
                return 'full';
            }

            $claseBloqueada->users()->attach($user->id);
            $claseBloqueada->decrement('capacidad_max');

            return 'ok';
        });

        if ($resultado === 'duplicate') {
            return back()->with('error', 'Ese usuario ya estaba apuntado.');
        }

        if ($resultado === 'full') {
            return back()->with('error', 'No quedan plazas libres en esa clase.');
        }

        return back()->with('success', 'Usuario añadido a la clase.');
    }

    /**
     * Quita un usuario de una clase y devuelve una plaza.
     */
    public function removeUserFromClass(GymClass $clase, User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No se pueden gestionar administradores en clases.');
        }

        $resultado = DB::transaction(function () use ($clase, $user) {
            // Mismo bloqueo para mantener capacidad consistente.
            $claseBloqueada = GymClass::query()->lockForUpdate()->findOrFail($clase->id);
            $existia = $claseBloqueada->users()->where('user_id', $user->id)->exists();

            if (!$existia) {
                return 'missing';
            }

            $claseBloqueada->users()->detach($user->id);
            $claseBloqueada->increment('capacidad_max');

            return 'ok';
        });

        if ($resultado === 'missing') {
            return back()->with('error', 'Ese usuario no estaba apuntado a esta clase.');
        }

        return back()->with('success', 'Usuario eliminado de la clase.');
    }

    /**
     * Calcula la siguiente fecha de cobro segun tarifa.
     */
    private function nextChargeDate(string $tarifa, ?Carbon $base = null): string
    {
        $fecha = ($base ?? now())->copy();

        return match ($tarifa) {
            'trimestral' => $fecha->addMonthsNoOverflow(3)->toDateString(),
            'anual' => $fecha->addYearNoOverflow()->toDateString(),
            default => $fecha->addMonthNoOverflow()->toDateString(),
        };
    }

    /**
     * Normaliza nombres de día para guardar siempre en formato ASCII.
     */
    private function normalizeWeekday(string $dia): string
    {
        return match (trim($dia)) {
            'Miercoles' => 'Miercoles',
            'Sabado' => 'Sabado',
            default => trim($dia),
        };
    }

    /**
     * Devuelve variantes utiles para filtrar datos antiguos y nuevos.
     */
    private function weekdayVariants(string $dia): array
    {
        return match ($this->normalizeWeekday($dia)) {
            'Miercoles' => ['Miercoles'],
            'Sabado' => ['Sabado'],
            default => [$this->normalizeWeekday($dia)],
        };
    }

    /**
     * Aprueba un pago manual pendiente.
     */
    public function approveManualPayment(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No aplica a administradores.');
        }

        $metodoLabel = $this->paymentMethodLabel($user->metodo_pago);

        $user->update([
            'payment_status' => 'al_dia',
            'next_payment_at' => $this->nextChargeDate($user->tarifa),
            'last_manual_payment_at' => now(),
        ]);

        return back()->with('success', "Pago recibido por {$metodoLabel}. Cuenta activada.");
    }

    /**
     * Devuelve nombre legible del método de pago.
     */
    private function paymentMethodLabel(?string $method): string
    {
        return match (strtolower((string) $method)) {
            'bizum' => 'Bizum',
            'paypal' => 'PayPal',
            'transferencia' => 'Transferencia',
            'tarjeta', 'stripe', 'visa' => 'Tarjeta',
            'efectivo' => 'Efectivo',
            default => 'Método manual',
        };
    }
}


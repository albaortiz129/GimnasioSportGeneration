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
        // Buscador del panel.
        $buscar = trim((string) $request->query('q', '')); // Elimina los espacios en blanco del texto del buscador.

        $discountsTablesReady = Schema::hasTable('discount_redemptions') // Verifica si existe la tabla de usos de descuentos.
            && Schema::hasTable('discount_codes'); // Verifica si existe la tabla de códigos de descuentos.

        $usuariosBaseQuery = User::query() // Consulta base de usuarios.
            ->where('is_admin', false) // Excluye a los administradores.
            ->when($buscar !== '', function ($query) use ($buscar) { // Si el buscador no está vacío, filtra por nombre, apellidos, email o DNI.
                $query->where(function ($sub) use ($buscar) {
                    $sub->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('apellidos', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('dni', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombre')
            ->orderBy('apellidos');

        // Clona la consulta base para poder añadir información de los descuentos si las tablas están disponibles.
        $usuariosQuery = clone $usuariosBaseQuery;
        if ($discountsTablesReady) {
            $usuariosQuery->with(['latestDiscountRedemption.discountCode']); // Asocia los descuentos con los usuarios.
        }

        try {
            $usuarios = $usuariosQuery->get(); // Ejecuta la consulta y obtiene los usuarios.
        } catch (QueryException $exception) {
            report($exception); // Registra la excepción.
            $discountsTablesReady = false; // Desactiva la información de los descuentos.
            $usuarios = (clone $usuariosBaseQuery)->get(); // Ejecuta la consulta sin los descuentos.
        }

        $impagados = collect(); // Crea una colección vacía de impagados.
        $billingColumnsReady = Schema::hasColumn('users', 'payment_status')
            && Schema::hasColumn('users', 'next_payment_at'); // Verifica si existen las columnas de pago.

        if ($billingColumnsReady) {
            try {
                // Consulta de clientes con impago o con fecha de cobro vencida.
                $impagadosQuery = User::query()
                    ->where('is_admin', false) // Excluye a los administradores.
                    ->where(function ($query) { // Si el buscador no está vacío, filtra por nombre, apellidos, email o DNI.
                        $query->where('payment_status', 'impagado') // Si el estado de pago es impagado.
                            ->orWhere('payment_status', 'pendiente') // O si el estado de pago es pendiente.
                            ->orWhere(function ($sub) { // O si el estado de pago no es "al_dia" y la fecha de cobro está vencida.
                            $sub->where('payment_status', '!=', 'al_dia')
                                ->whereNotNull('next_payment_at')
                                ->whereDate('next_payment_at', '<', today());
                        });
                    })
                    ->orderByRaw("
                        CASE payment_status
                            WHEN 'impagado' THEN 1
                            WHEN 'pendiente' THEN 2
                            WHEN 'al_dia' THEN 3
                            ELSE 4
                        END
                    ") // Ordena los usuarios por estado de pago.
                    ->orderBy('next_payment_at'); // Y por fecha de cobro.

                if ($discountsTablesReady) {
                    $impagadosQuery->with(['latestDiscountRedemption.discountCode']); // Asocia los descuentos con los usuarios.
                }

                $impagados = $impagadosQuery->get(); // Ejecuta la consulta y obtiene los usuarios.

            } catch (QueryException $exception) {
                report($exception); // Registra la excepción.
                $billingColumnsReady = false; // Desactiva la información de los descuentos.
                $impagados = collect(); // Crea una colección vacía de impagados.
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
     * Muestra el formulario para crear usuario.
     */
    public function create()
    {
        return view('admin.create-user');
    }

    /**
     * Crea un nuevo socio desde admin.
     */
    public function store(Request $request)
    {
        // Validación completa del formulario de alta.
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
                        $fail('El DNI no es válido.');
                    }
                },
            ],
            'fecha_nacimiento' => 'required|date',
            'telefono' => ['required', 'regex:/^[6789]\d{8}$/'],
            'email' => 'required|email|unique:users,email',
            'domicilio' => 'required|string|max:255',
            'tarifa' => 'required|in:mensual,trimestral,anual',
            'metodo_pago' => 'required|in:visa,efectivo',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
        ], [
            'telefono.regex' => 'El teléfono es incorrecto.',
            'dni.regex' => 'El DNI es incorrecto.',
            'metodo_pago.in' => 'Selecciona un método de pago válido.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
        ]);

        // Normaliza los datos
        $data['nombre'] = trim($data['nombre']);
        $data['apellidos'] = trim($data['apellidos']);
        $data['dni'] = strtoupper(trim($data['dni']));
        $data['email'] = strtolower(trim($data['email']));
        $data['telefono'] = trim($data['telefono']);
        $data['domicilio'] = trim($data['domicilio']);

        $user = User::create([ // Crea el usuario en la base de datos
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

        return redirect()->route('admin.user.edit', $user)
            ->with('success', 'Usuario creado correctamente.');
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
            return back()->with('error', 'No se permite actualizar los datos de los administradores.');
        }

        $request->merge([
            'metodo_pago' => strtolower(trim((string) $request->input('metodo_pago', ''))),
        ]);

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
            'metodo_pago' => 'required|in:visa,efectivo',
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
            'metodo_pago.in' => 'Selecciona un método de pago válido.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'Debe incluir mayúscula, minúscula, número y carácter especial.',
        ]);

        // Limpia y normaliza los datos
        $data['nombre'] = trim($data['nombre']);
        $data['apellidos'] = trim($data['apellidos']);
        $data['dni'] = strtoupper(trim($data['dni']));
        $data['email'] = strtolower(trim($data['email']));
        $data['telefono'] = trim($data['telefono']);
        $data['domicilio'] = trim($data['domicilio']);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
            $data['must_change_password'] = true; // Fuerza el cambio de contraseña al entrar por primera vez.
        } else {
            unset($data['password']); // Si no se introduce nueva contraseña, no se cambia la existente.
        }

        $user->update($data);

        return redirect()->route('admin.dashboard')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Cambia la tarifa de un usuario.
     */
    public function changePlan(Request $request, User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No se permite cambiar el plan de los administradores.');
        }

        $data = $request->validate([
            'tarifa' => 'required|in:mensual,trimestral,anual,cancelada',
        ]);

        // Calcula la siguiente fecha solo cuando hay un plan activo.
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
            return back()->with('error', 'No se permite registrar cobros a los administradores.');
        }

        $data = $request->validate([
            'tarifa' => 'required|in:mensual,trimestral,anual',
            'metodo_manual' => 'required|in:efectivo,visa',
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

        $metodoLabel = $this->paymentMethodLabel($data['metodo_manual']); // Obtiene el nombre del método de pago.
        $this->sendPaymentApprovedEmail(
            $user,
            $metodoLabel,
            'Cobro manual registrado por administración', // Asunto del correo.
            'manualCharge' // Nombre del template.
        );

        return back()->with('success', 'Cobro manual registrado, pago al día.');
    }

    /**
     * Renueva manualmente la suscripción.
     */
    public function renewSubscription(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No se permite renovar la suscripción de los administradores.');
        }

        // Si ya existe una fecha futura, no se acumulan meses al pulsar varias veces.
        if ($user->next_payment_at) {
            $fechaActual = Carbon::parse($user->next_payment_at); // Convierte la fecha a Carbon (librería de PHP de fechas y horas).

            if ($fechaActual->isToday() || $fechaActual->isFuture()) { // Si la fecha es hoy o futura
                $user->update([
                    'payment_status' => 'al_dia',
                    'next_payment_at' => $fechaActual->toDateString(), // Actualiza la fecha.
                ]);

                $metodoLabel = $this->paymentMethodLabel($user->metodo_pago); // Obtiene el nombre del método de pago.
                $this->sendPaymentApprovedEmail(
                    $user,
                    $metodoLabel,
                    'Suscripción renovada por administración', // Asunto del correo.
                    'renewSubscription:fecha_vigente' // Nombre del template.
                );

                return back()->with('success', 'Pago en curso. La fecha de renovación se mantiene.');
            }
        }

        $user->update([
            'payment_status' => 'al_dia',
            'next_payment_at' => $this->nextChargeDate($user->tarifa, now()), // Calcula la siguiente fecha de cobro.
        ]);

        $metodoLabel = $this->paymentMethodLabel($user->metodo_pago); // Obtiene el nombre del método de pago.
        $this->sendPaymentApprovedEmail(
            $user,
            $metodoLabel,
            'Suscripción renovada por administración', // Asunto del correo.
            'renewSubscription:nueva_fecha' // Nombre del template.
        );

        return back()->with('success', 'Suscripción renovada.');
    }

    /**
     * Marca un usuario como impagado.
     */
    public function markUnpaid(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No se permite marcar como impagado a un administrador.');
        }

        $user->update([
            'payment_status' => 'impagado', // Pasa a estado de impago.
        ]);

        return back()->with('success', 'Cliente marcado como impagado.'); // Notifica al usuario.
    }

    /**
     * Elimina un usuario socio.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) { // Comprueba si el usuario es el mismo que está intentando eliminar.
            return back()->with('error', 'No puedes eliminarte a ti mismo porque eres el administrador.');
        }

        if ($user->is_admin) { // Comprueba si el usuario es administrador.
            return back()->with('error', 'No puedes eliminar otro administrador.');
        }

        $user->delete(); // Elimina el usuario.

        return redirect()->route('admin.dashboard')->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Pantalla de administración de clases.
     */
    public function classesIndex(Request $request)
    {
        $dia = $request->query('dia'); // Obtiene el día de la semana de la solicitud.

        $diasSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

        $ordenDias = "
            CASE dia_semana
                WHEN 'Lunes' THEN 1
                WHEN 'Martes' THEN 2
                WHEN 'Miercoles' THEN 3
                WHEN 'Jueves' THEN 4
                WHEN 'Viernes' THEN 5
                WHEN 'Sabado' THEN 6
                WHEN 'Domingo' THEN 7
                ELSE 8
            END
        ";

        // Carga clases con usuarios asociados.
        $clases = GymClass::with([
            'users' => function ($query) {
                $query->where('is_admin', false) // Obtiene solo los usuarios no administradores.
                    ->orderBy('nombre') // Ordena por nombre.
                    ->orderBy('apellidos'); // Ordena por apellidos.
            }
        ])
            ->when($dia, fn($query) => $query->whereIn('dia_semana', $this->weekdayVariants($dia))) // Aplica el filtro por día.
            ->orderByRaw($ordenDias) // Ordena por día de la semana.
            ->orderBy('hora_inicio') // Ordena por hora de inicio.
            ->get();

        $usuarios = User::where('is_admin', false) // Obtiene solo los usuarios no administradores.
            ->orderBy('nombre') // Ordena por nombre.
            ->orderBy('apellidos') // Ordena por apellidos.
            ->get();

        return view('admin.classes', compact('clases', 'usuarios', 'dia', 'diasSemana')); // Muestra la vista de clases.
    }

    /**
     * Crea una nueva clase
     */
    public function classStore(Request $request)
    {
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

        $data['dia_semana'] = $this->normalizeWeekday($data['dia_semana']); // Normaliza el día de la semana.

        GymClass::create($data); // Crea la clase.

        return back()->with('success', 'Clase creada correctamente.'); // Notifica al usuario.
    }

    /**
     * Actualiza una clase existente.
     */
    public function classUpdate(Request $request, GymClass $clase)
    {
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

        $data['dia_semana'] = $this->normalizeWeekday($data['dia_semana']); // Normaliza el día de la semana.

        $clase->update($data); // Actualiza la clase.

        return back()->with('success', 'Clase actualizada correctamente.'); // Notifica al usuario.
    }

    /**
     * Elimina una clase del calendario.
     */
    public function classDestroy(GymClass $clase)
    {
        $clase->delete(); // Elimina la clase.

        return back()->with('success', 'Clase eliminada correctamente.'); // Notifica al usuario.
    }

    /**
     * Añade un usuario a una clase y descuenta una plaza.
     */
    public function addUserToClass(Request $request, GymClass $clase)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::where('is_admin', false)->findOrFail($request->user_id); // Obtiene solo los usuarios no administradores.

        $resultado = DB::transaction(function () use ($clase, $user) {
            // Bloqueo de la clase para evitar sobrecupo por concurrencia.
            $claseBloqueada = GymClass::query()->lockForUpdate()->findOrFail($clase->id);

            if ($claseBloqueada->users()->where('user_id', $user->id)->exists()) { // Comprueba si el usuario ya está apuntado.
                return 'duplicate';
            }

            if ($claseBloqueada->capacidad_max <= 0) { // Comprueba si la clase está llena.
                return 'full';
            }

            $claseBloqueada->users()->attach($user->id); // Añade el usuario a la clase.
            $claseBloqueada->decrement('capacidad_max'); // Descuenta una plaza.

            return 'ok';
        });

        if ($resultado === 'duplicate') { // Si el usuario ya está apuntado se muestra mensaje.
            return back()->with('error', 'Ese usuario ya estaba apuntado.');
        }

        if ($resultado === 'full') { // Si la clase está llena se muestra mensaje.
            return back()->with('error', 'No quedan plazas libres en esta clase.');
        }

        return back()->with('success', 'Usuario añadido a la clase.');
    }

    /**
     * Quita un usuario de una clase y devuelve una plaza.
     */
    public function removeUserFromClass(GymClass $clase, User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No se puede quitar a un administrador de una clase.');
        }

        $resultado = DB::transaction(function () use ($clase, $user) {
            // Mismo bloqueo para mantener capacidad consistente.
            $claseBloqueada = GymClass::query()->lockForUpdate()->findOrFail($clase->id);
            $existia = $claseBloqueada->users()->where('user_id', $user->id)->exists(); // Comprueba si el usuario está apuntado.

            if (!$existia) { // Si el usuario no está apuntado se muestra mensaje.
                return 'missing';
            }

            $claseBloqueada->users()->detach($user->id); // Quita al usuario de la clase.
            $claseBloqueada->increment('capacidad_max'); // Devuelve una plaza.

            return 'ok';
        });

        if ($resultado === 'missing') {
            return back()->with('error', 'Este usuario no estaba apuntado a esta clase.');
        }

        return back()->with('success', 'Usuario eliminado de la clase correctamente.');
    }

    /**
     * Calcula la siguiente fecha de cobro según tarifa.
     */
    private function nextChargeDate(string $tarifa, ?Carbon $base = null): string
    {
        $fecha = ($base ?? now())->copy(); // Copia la fecha base o la fecha actual.

        return match ($tarifa) { // Devuelve la fecha siguiente según la tarifa.
            'trimestral' => $fecha->addMonthsNoOverflow(3)->toDateString(), // Suma 3 meses a la fecha actual.
            'anual' => $fecha->addYearNoOverflow()->toDateString(), // Suma 1 año a la fecha actual.
            default => $fecha->addMonthNoOverflow()->toDateString(), // Suma 1 mes a la fecha actual.
        };
    }

    /**
     * Normaliza los nombres de día con o sin tildes para guardar un formato único en la base de datos.
     */
    private function normalizeWeekday(string $dia): string
    {
        return match (trim($dia)) {
            'Miércoles',
            'Miercoles' => 'Miercoles',
            'Sábado',
            'Sabado' => 'Sabado',
            default => trim($dia),
        };
    }

    /**
     * Devuelve variantes para filtrar días.
     */
    private function weekdayVariants(string $dia): array
    {
        return match ($this->normalizeWeekday($dia)) {
            'Miercoles' => ['Miercoles', 'Miércoles'], // Devuelve las variantes de miércoles.
            'Sabado' => ['Sabado', 'Sábado'], // Devuelve las variantes de sábado.
            default => [$this->normalizeWeekday($dia)], // Devuelve la variante normalizada.
        };
    }

    /**
     * Aprueba un pago manual pendiente.
     */
    public function approveManualPayment(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'No se puede aprobar el pago de un administrador.');
        }

        $metodoLabel = $this->paymentMethodLabel($user->metodo_pago); // Obtiene el método de pago.

        $user->update([
            'payment_status' => 'al_dia', // Cambia el estado de pago a "al día".
            'next_payment_at' => $this->nextChargeDate($user->tarifa), // Calcula la siguiente fecha de cobro.
            'last_manual_payment_at' => now(), // Establece la fecha actual como la última fecha de pago manual.
        ]);

        $this->sendPaymentApprovedEmail( // Envía el correo de pago aprobado.
            $user,
            $metodoLabel,
            'Pago pendiente validado por administración',
            'approveManualPayment'
        );

        return back()->with('success', "Pago recibido por {$metodoLabel}. Cuenta activada."); // Notifica al usuario.
    }

    /**
     * Devuelve el nombre legible del método de pago.
     */
    private function paymentMethodLabel(?string $method): string
    {
        return match (strtolower((string) $method)) {
            'visa' => 'Tarjeta',
            'efectivo' => 'Efectivo',
            default => 'Método manual',
        };
    }

    /**
     * Envía el correo de pago aprobado y registra errores.
     */
    private function sendPaymentApprovedEmail(User $user, string $metodo, string $origen, string $context): void
    {
        try { // Intenta enviar el correo de pago aprobado.
            Mail::send('emails.payment-approved', [
                'nombre' => $user->nombre,
                'metodo' => $metodo,
                'tarifa' => ucfirst((string) $user->tarifa), // Obtiene el método de pago.
                'proximoCobro' => optional($user->next_payment_at)->format('d/m/Y') ?? 'Sin fecha', // Calcula la siguiente fecha de cobro.
                'origen' => $origen, // Obtiene el método de pago.
            ], function ($message) use ($user) { // Envía el correo de pago aprobado.
                $message->to($user->email); // Envía el correo al usuario.
                $message->subject('Pago aprobado - SeaFit'); // Asigna el asunto del correo.
            });
        } catch (\Throwable $e) { // Captura errores al enviar el correo de pago aprobado.
            Log::error("Error al enviar correo de pago aprobado ({$context}).", [
                'user_id' => $user->id, // Obtiene el usuario.
                'email' => $user->email, // Obtiene el correo del usuario.
                'error' => $e->getMessage(), // Obtiene el error.
            ]);
        }
    }
}


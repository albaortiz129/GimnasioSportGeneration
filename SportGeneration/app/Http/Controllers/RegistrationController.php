<?php

/**
 * Controlador de registro de socios.
 * Valida datos, crea usuarios y procesa el pago inicial.
 */
namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    /**
     * Comprueba si el DNI y/o email ya existen para avisar en el paso 1 del registro.
     */
    public function checkAvailability(Request $request)
    {
        $data = $request->validate([ // Valida los datos recibidos.
            'dni' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $dni = strtoupper(trim((string) ($data['dni'] ?? ''))); // Convierte el DNI a mayúsculas y elimina espacios en blanco.
        $email = strtolower(trim((string) ($data['email'] ?? ''))); // Convierte el email a minúsculas y elimina espacios en blanco.

        return response()->json([
            'dni_disponible' => $dni === '' ? null : !User::where('dni', $dni)->exists(), // Comprueba si el DNI está disponible.
            'email_disponible' => $email === '' ? null : !User::where('email', $email)->exists(), // Comprueba si el email está disponible.
        ]);
    }

    /**
     * Registra un nuevo socio y, según el método de pago, activa la suscripción.
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'dni' => [
                    'required',
                    'string',
                    'size:9',
                    'regex:/^[0-9]{8}[A-Za-z]$/',
                    'unique:users,dni',
                    function ($attribute, $value, $fail) { // Valida que el DNI sea correcto.
                        if (!$this->isValidDni((string) $value)) {
                            $fail('El DNI no es válido (letra incorrecta).');
                        }
                    },
                ],
                'fecha_nacimiento' => 'required|date',
                'telefono' => ['required', 'regex:/^[6789]\d{8}$/'],
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
                ],
                'domicilio' => 'required|string|max:255',
                'tarifa' => 'required|in:mensual,trimestral,anual',
                'metodo_pago' => 'required|in:visa,efectivo',
                'cupon' => 'nullable|string|max:100',
                'stripeCodigo' => 'nullable|string',
            ], [
                'dni.unique' => 'Ya existe un usuario registrado con ese DNI.',
                'dni.regex' => 'El DNI es incorrecto.',
                'telefono.regex' => 'El teléfono es incorrecto.',
                'email.unique' => 'Ya existe un usuario registrado con ese email.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
            ]);

            if ($validator->fails()) { // Si falla la validación, devuelve los errores.
                return response()->json([
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($request->metodo_pago === 'visa' && !$request->filled('stripeCodigo')) { // Si el método de pago es visa y no se ha proporcionado un código de Stripe.
                return response()->json([
                    'error' => 'Falta el método de pago de Stripe.',
                ], 422);
            }

            $userIdParaCorreoPagoAprobado = null; // Guardará el usuario para enviar correo tras confirmar el registro.

            $response = DB::transaction(function () use ($request, &$userIdParaCorreoPagoAprobado) { // Inicia una transacción para garantizar la integridad de los datos.
                $discountCode = null; // Inicializa la variable que contendrá el código de descuento.
                $mensajeDescuento = ''; // Inicializa la variable que contendrá el mensaje de descuento.

                if ($request->filled('cupon')) { // Si se ha proporcionado un código de descuento.
                    $discountCode = DiscountCode::byCode($request->cupon)->first(); // Obtiene el código de descuento.

                    if (!$discountCode || !$discountCode->isActiveNow()) { // Si el código de descuento no es válido o está caducado.
                        return response()->json([
                            'error' => 'Cupón no válido o caducado.',
                        ], 422);
                    }

                    $mensajeDescuento = 'Cupón validado correctamente.';
                }

                $tarifa = (string) $request->tarifa; // Obtiene la tarifa del plan.
                $metodoPago = (string) $request->metodo_pago; // Obtiene el método de pago.
                $precioBase = $this->planBaseAmount($tarifa); // Obtiene el precio base del plan.
                $descuentoAplicado = $discountCode
                    ? $discountCode->calculateDiscountAmount($precioBase) // Calcula el descuento si el cupón es válido.
                    : 0.0; // Si no hay descuento, se establece en 0.0.

                // Crea un nuevo usuario.
                $user = new User();
                $user->nombre = trim((string) $request->nombre);
                $user->apellidos = trim((string) $request->apellidos);
                $user->dni = strtoupper(trim((string) $request->dni));
                $user->fecha_nacimiento = $request->fecha_nacimiento;
                $user->telefono = trim((string) $request->telefono);
                $user->email = strtolower(trim((string) $request->email));
                $user->domicilio = trim((string) $request->domicilio);
                $user->tarifa = $tarifa;
                $user->metodo_pago = $metodoPago;
                $user->password = Hash::make((string) $request->password);

                // Si el alta es en efectivo, se guarda ese método manual como valor inicial.
                if ($metodoPago === 'efectivo') {
                    $user->manual_payment_methods = [[
                        'code' => 'efectivo',
                        'value' => null,
                    ]];
                }

                $esTarjeta = $metodoPago === 'visa'; // Si el método de pago es tarjeta.
                $user->payment_status = $esTarjeta ? 'al_dia' : 'pendiente'; // Estado del pago.
                $user->next_payment_at = $esTarjeta ? $this->nextChargeFromPlan($tarifa) : null; // Fecha del próximo pago.
                $user->save(); // Guarda el usuario.

                $mensajeFinal = 'Socio registrado con éxito.'; // Mensaje final.

                if ($esTarjeta && $request->filled('stripeCodigo')) { // Si el método de pago es tarjeta y se ha proporcionado un código de Stripe.
                    $priceId = $this->priceIdFromPlan($tarifa); // Obtiene el ID del plan.

                    if ($priceId) { // Si el ID del plan es válido.
                        try {
                            $user->createAsStripeCustomer(); // Crea el cliente en Stripe.
                            $newSubscription = $user->newSubscription('default', $priceId); // Crea una nueva suscripción.

                            if ($discountCode && $discountCode->stripe_coupon_id) { // Si el cupón es válido.
                                $newSubscription->withCoupon($discountCode->stripe_coupon_id); // Se aplica el cupón.
                            }

                            $newSubscription->create((string) $request->stripeCodigo); // Se crea la suscripción.

                            $user->payment_status = 'al_dia'; // Se actualiza el estado del pago.
                            $user->next_payment_at = $this->nextChargeFromPlan($tarifa); // Se actualiza la fecha del próximo pago.
                            $user->save(); // Guarda el usuario.
                            $userIdParaCorreoPagoAprobado = $user->id;

                            $mensajeFinal = 'Socio registrado y suscripción activada con éxito.';
                        } catch (\Throwable $e) { // Si falla Stripe, no se pierde el alta; queda pendiente de revisión.
                            $user->payment_status = 'pendiente'; // Se actualiza el estado del pago.
                            $user->next_payment_at = null; // Se actualiza la fecha del próximo pago.
                            $user->save(); // Guarda el usuario.

                            $mensajeFinal = 'Registro creado, pero no se pudo confirmar el pago con tarjeta. Contacta con soporte.'; // Mensaje final.
                        }
                    }
                } elseif ($metodoPago === 'efectivo') { // Si el método de pago es efectivo.
                    $mensajeFinal = 'Registro recibido. Tu cuenta queda pendiente hasta validar el pago en efectivo en recepción.';
                }

                if ($discountCode) { // Si el cupón es válido.
                    $discountCode->markUsed($user, 'registro', $descuentoAplicado); // Se marca como usado.
                }

                return response()->json([ // Devuelve una respuesta JSON con el mensaje final, el descuento y el ID del usuario.
                    'mensaje' => $mensajeFinal,
                    'descuento' => $mensajeDescuento,
                    'usuario_id' => $user->id,
                    'email' => $user->email,
                ], 201);
            });

            // Si el alta con tarjeta se confirmó, enviamos el correo de pago aprobado.
            if (!empty($userIdParaCorreoPagoAprobado)) {
                $userParaCorreo = User::find($userIdParaCorreoPagoAprobado);

                if ($userParaCorreo) {
                    $this->sendPaymentApprovedEmail(
                        $userParaCorreo,
                        'Tarjeta',
                        'Pago con tarjeta confirmado en el registro'
                    );
                }
            }

            return $response;
        } catch (\Throwable $e) {
            Log::error('Error en el registro Sport Generation', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'No se pudo completar el registro. Inténtalo de nuevo en unos minutos.',
            ], 500);
        }
    }

    /**
     * Obtiene el `price_id` de Stripe según la tarifa elegida.
     */
    private function priceIdFromPlan(string $tarifa): ?string
    {
        return match ($tarifa) {
            'mensual' => 'price_1TEXJALV86wly52BTx1BvxYJ',
            'trimestral' => 'price_1TEXLwLV86wly52BLC3ihcbo',
            'anual' => 'price_1TEXLNLV86wly52BXUvZyG9R',
            default => null,
        };
    }

    /**
     * Importe base del plan antes de descuentos.
     */
    private function planBaseAmount(string $tarifa): float
    {
        return match ($tarifa) {
            'trimestral' => 75.00,
            'anual' => 250.00,
            default => 29.99,
        };
    }

    /**
     * Calcula la fecha del próximo cobro según la tarifa.
     */
    private function nextChargeFromPlan(string $tarifa): ?string
    {
        $fecha = now(); // Fecha actual.

        return match ($tarifa) { // Devuelve la fecha del próximo cobro según la tarifa.
            'trimestral' => $fecha->addMonthsNoOverflow(3)->toDateString(), // Suma 3 meses a la fecha actual.
            'anual' => $fecha->addYearNoOverflow()->toDateString(), // Suma 1 año a la fecha actual.
            'mensual' => $fecha->addMonthNoOverflow()->toDateString(), // Suma 1 mes a la fecha actual.
            default => null, // Si la tarifa no es válida, devuelve null.
        };
    }

    /**
     * Valida matemáticamente la letra del DNI.
     */
    private function isValidDni(string $dni): bool
    {
        $dni = strtoupper(trim($dni)); // Convierte el DNI a mayúsculas y elimina los espacios en blanco.

        if (!preg_match('/^[0-9]{8}[A-Z]$/', $dni)) { // Comprueba que el DNI tenga el formato correcto.
            return false;
        }

        $numero = (int) substr($dni, 0, 8); // Obtiene el número del DNI.
        $letra = substr($dni, 8, 1); // Obtiene la letra del DNI.
        $letrasValidas = 'TRWAGMYFPDXBNJZSQVHLCKE'; // Letras válidas para el DNI.

        return $letra === $letrasValidas[$numero % 23]; // Comprueba que la letra sea correcta.
    }

    /**
     * Envía correo de pago aprobado cuando el alta con tarjeta termina correctamente.
     */
    private function sendPaymentApprovedEmail(User $user, string $metodo, string $origen): void
    {
        try {
            $proximoCobro = 'Sin fecha';

            if (!empty($user->next_payment_at)) {
                $proximoCobro = Carbon::parse((string) $user->next_payment_at)->format('d/m/Y');
            }

            Mail::send('emails.payment-approved', [
                'nombre' => $user->nombre,
                'metodo' => $metodo,
                'tarifa' => ucfirst((string) $user->tarifa),
                'proximoCobro' => $proximoCobro,
                'origen' => $origen,
            ], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Pago aprobado - Sport Generation');
            });

            Log::info('Correo de pago aprobado enviado en registro.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            // Si falla el correo, no se rompe el registro del socio.
            Log::error('Error al enviar correo de pago aprobado en registro.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

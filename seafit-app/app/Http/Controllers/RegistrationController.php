<?php

/**
 * Controlador de registro de socios.
 * Valida datos, crea usuarios y procesa el pago inicial.
 */
namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    /**
     * Comprueba si DNI y/o email ya existen para avisar en el paso 1 del registro.
     */
    public function checkAvailability(Request $request)
    {
        $data = $request->validate([
            'dni' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $dni = strtoupper(trim((string) ($data['dni'] ?? '')));
        $email = strtolower(trim((string) ($data['email'] ?? '')));

        return response()->json([
            'dni_disponible' => $dni === '' ? null : !User::where('dni', $dni)->exists(),
            'email_disponible' => $email === '' ? null : !User::where('email', $email)->exists(),
        ]);
    }

    /**
     * Registra un nuevo socio y, según el método de pago, activa suscripción.
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
                    function ($attribute, $value, $fail) {
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
                'metodo_pago' => 'required|in:visa,bizum,paypal,efectivo',
                'cupon' => 'nullable|string|max:100',
                'stripeCodigo' => 'nullable|string',
            ], [
                'dni.unique' => 'Ya existe un usuario registrado con ese DNI.',
                'dni.regex' => 'El DNI debe tener 8 números y 1 letra (ej: 12345678Z).',
                'telefono.regex' => 'El teléfono debe tener 9 dígitos y empezar por 6, 7, 8 o 9.',
                'email.unique' => 'Ya existe un usuario registrado con ese email.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($request->metodo_pago === 'visa' && !$request->filled('stripeCodigo')) {
                return response()->json([
                    'error' => 'Falta el método de pago de Stripe.',
                ], 422);
            }

            return DB::transaction(function () use ($request) {
                $discountCode = null;
                $mensajeDescuento = '';

                if ($request->filled('cupon')) {
                    $discountCode = DiscountCode::byCode($request->cupon)->first();

                    if (!$discountCode || !$discountCode->isActiveNow()) {
                        return response()->json([
                            'error' => 'Cupón no válido o caducado.',
                        ], 422);
                    }

                    $mensajeDescuento = 'Cupón validado correctamente.';
                }

                $tarifa = (string) $request->tarifa;
                $metodoPago = (string) $request->metodo_pago;
                $precioBase = $this->planBaseAmount($tarifa);
                $descuentoAplicado = $discountCode
                    ? $discountCode->calculateDiscountAmount($precioBase)
                    : 0.0;

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

                // Si el alta es manual, dejamos el método guardado desde el primer día.
                $manualMethods = $this->initialManualMethodsForRegistration(
                    $metodoPago,
                    (string) $user->telefono,
                    (string) $user->email
                );
                if ($manualMethods !== []) {
                    $user->manual_payment_methods = $manualMethods;
                }

                $esTarjeta = $metodoPago === 'visa';
                $user->payment_status = $esTarjeta ? 'al_dia' : 'pendiente';
                $user->next_payment_at = $esTarjeta ? $this->nextChargeFromPlan($tarifa) : null;
                $user->save();

                $mensajeFinal = 'Socio registrado con éxito.';

                if ($esTarjeta && $request->filled('stripeCodigo')) {
                    $priceId = $this->priceIdFromPlan($tarifa);

                    if ($priceId) {
                        try {
                            $user->createAsStripeCustomer();

                            $newSubscription = $user->newSubscription('default', $priceId);

                            if ($discountCode && $discountCode->stripe_coupon_id) {
                                $newSubscription->withCoupon($discountCode->stripe_coupon_id);
                            }

                            $newSubscription->create((string) $request->stripeCodigo);

                            $user->payment_status = 'al_dia';
                            $user->next_payment_at = $this->nextChargeFromPlan($tarifa);
                            $user->save();

                            $mensajeFinal = 'Socio registrado y suscripción activada con éxito.';
                        } catch (\Throwable $e) {
                            // Si falla Stripe, no se pierde el alta; queda pendiente de revision.
                            $user->payment_status = 'pendiente';
                            $user->next_payment_at = null;
                            $user->save();

                            $mensajeFinal = 'Registro creado, pero no se pudo confirmar el pago con tarjeta. Contacta con soporte.';
                        }
                    }
                } elseif ($metodoPago === 'bizum') {
                    $mensajeFinal = 'Registro recibido. Envía el Bizum al 600 000 000 con tu DNI como concepto para activar tu cuenta.';
                } elseif ($metodoPago === 'paypal') {
                    $mensajeFinal = 'Registro recibido. Tu cuenta queda pendiente de validación hasta confirmar el pago por PayPal.';
                } elseif ($metodoPago === 'efectivo') {
                    $mensajeFinal = 'Registro recibido. Tu cuenta queda pendiente hasta validar el pago en efectivo en recepción.';
                }

                if ($discountCode) {
                    $discountCode->markUsed($user, 'registro', $descuentoAplicado);
                }

                // Envío de bienvenida con el mismo canal de correo que el reset de contraseña.
                $welcomeEmailSent = $this->sendWelcomeEmail($user);

                if (!$welcomeEmailSent) {
                    $mensajeFinal .= ' Aviso: no se pudo enviar el correo de bienvenida en este momento.';
                }

                return response()->json([
                    'mensaje' => $mensajeFinal,
                    'descuento' => $mensajeDescuento,
                    'usuario_id' => $user->id,
                    'email' => $user->email,
                    'welcome_email_sent' => $welcomeEmailSent,
                ], 201);
            });
        } catch (\Throwable $e) {
            Log::error('Error en Registro SeaFit', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'No se pudo completar el registro. Inténtalo de nuevo en unos minutos.',
            ], 500);
        }
    }

    /**
     * Envía el correo de bienvenida al nuevo socio.
     */
    private function sendWelcomeEmail(User $user): bool
    {
        try {
            Mail::send('emails.bienvenida', ['user' => $user], function ($message) use ($user) {
                // Solo email (sin nombre) para evitar errores de cabecera por caracteres especiales.
                $message->to($user->email);
                $message->subject('Bienvenido a SeaFit');
            });

            return true;
        } catch (\Throwable $mailError) {
            Log::warning('No se pudo enviar email de bienvenida', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $mailError->getMessage(),
            ]);

            return false;
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
        $fecha = now();

        return match ($tarifa) {
            'trimestral' => $fecha->addMonthsNoOverflow(3)->toDateString(),
            'anual' => $fecha->addYearNoOverflow()->toDateString(),
            'mensual' => $fecha->addMonthNoOverflow()->toDateString(),
            default => null,
        };
    }

    /**
     * Devuelve el método manual inicial para mostrarlo ya guardado en el panel.
     */
    private function initialManualMethodsForRegistration(string $metodoPago, string $telefono, string $email): array
    {
        return match ($metodoPago) {
            'bizum' => [[
                'code' => 'bizum',
                'value' => preg_replace('/\D+/', '', $telefono),
            ]],
            'paypal' => [[
                'code' => 'paypal',
                'value' => strtolower(trim($email)),
            ]],
            'efectivo' => [[
                'code' => 'efectivo',
                'value' => null,
            ]],
            default => [],
        };
    }

    /**
     * Valida matematicamente la letra del DNI.
     */
    private function isValidDni(string $dni): bool
    {
        $dni = strtoupper(trim($dni));

        if (!preg_match('/^[0-9]{8}[A-Z]$/', $dni)) {
            return false;
        }

        $numero = (int) substr($dni, 0, 8);
        $letra = substr($dni, 8, 1);
        $letrasValidas = 'TRWAGMYFPDXBNJZSQVHLCKE';

        return $letra === $letrasValidas[$numero % 23];
    }
}

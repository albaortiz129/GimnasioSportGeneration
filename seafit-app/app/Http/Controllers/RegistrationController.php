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
     * Registra un nuevo socio y, segun el metodo de pago, activa suscripcion.
     */
    public function register(Request $request)
    {
        try {
            // Valida los datos del formulario de registro.
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'dni' => 'required|string|unique:users,dni',
                'fecha_nacimiento' => 'required|date',
                'telefono' => 'required|string|max:20',
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
                ],
                'domicilio' => 'required|string|max:255',
                'tarifa' => 'required|string',
                'metodo_pago' => 'required|string',
                'cupon' => 'nullable|string',
                'stripeCodigo' => 'nullable|string',
            ], [
                'dni.unique' => 'Ya existe un usuario registrado con ese DNI.',
                'email.unique' => 'Ya existe un usuario registrado con ese email.',
                'password.confirmed' => 'Las contrasenas no coinciden.',
            ]);

            if ($validator->fails()) {
                // Devuelve errores por campo para pintarlos en el frontend.
                return response()->json([
                    'error' => 'Errores de validacion',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (in_array($request->metodo_pago, ['visa', 'amex'], true) && !$request->filled('stripeCodigo')) {
                // Con tarjeta, Stripe debe enviar el payment method id.
                return response()->json([
                    'error' => 'Falta el metodo de pago de Stripe.',
                ], 422);
            }

            return DB::transaction(function () use ($request) {
                $discountCode = null;
                $mensajeDescuento = '';

                if ($request->filled('cupon')) {
                    // Se busca el cupon escrito por el usuario.
                    $discountCode = DiscountCode::byCode($request->cupon)->first();

                    if (!$discountCode || !$discountCode->isActiveNow()) {
                        return response()->json([
                            'error' => 'Cupon no valido o caducado.',
                        ], 422);
                    }

                    $mensajeDescuento = 'Cupon validado correctamente.';
                }

                $precioBase = $this->planBaseAmount($request->tarifa);
                $descuentoAplicado = $discountCode
                    ? $discountCode->calculateDiscountAmount($precioBase)
                    : 0.0;

                // Crear al usuario.
                $user = new User();
                $user->nombre = $request->nombre;
                $user->apellidos = $request->apellidos;
                $user->dni = $request->dni;
                $user->fecha_nacimiento = $request->fecha_nacimiento;
                $user->telefono = $request->telefono;
                $user->email = $request->email;
                $user->domicilio = $request->domicilio;
                $user->tarifa = $request->tarifa;
                $user->metodo_pago = $request->metodo_pago;
                $user->password = Hash::make($request->password);

                $esTarjeta = in_array($request->metodo_pago, ['visa', 'amex'], true);
                // Si no es tarjeta, queda pendiente hasta validacion manual/admin.
                $user->payment_status = $esTarjeta ? 'al_dia' : 'pendiente';
                $user->next_payment_at = $esTarjeta ? $this->nextChargeFromPlan($request->tarifa) : null;
                $user->save();

                $mensajeFinal = 'Socio registrado con exito.';

                // Flujo de pago segun metodo seleccionado.
                if ($esTarjeta && $request->filled('stripeCodigo')) {
                    $priceId = $this->priceIdFromPlan($request->tarifa);

                    if ($priceId) {
                        try {
                            // Flujo completo en Stripe: cliente + suscripcion.
                            $user->createAsStripeCustomer();

                            $newSubscription = $user->newSubscription('default', $priceId);

                            if ($discountCode && $discountCode->stripe_coupon_id) {
                                $newSubscription->withCoupon($discountCode->stripe_coupon_id);
                            }

                            $newSubscription->create($request->stripeCodigo);

                            $user->payment_status = 'al_dia';
                            $user->next_payment_at = $this->nextChargeFromPlan($request->tarifa);
                            $user->save();

                            $mensajeFinal = 'Socio registrado y suscripcion activada con exito.';
                        } catch (\Throwable $e) {
                            // Si falla Stripe, el usuario se mantiene pero en pendiente.
                            $user->payment_status = 'pendiente';
                            $user->next_payment_at = null;
                            $user->save();

                            $mensajeFinal = 'Registro creado, pero el pago con tarjeta no se pudo confirmar. Revisa tu metodo de pago.';
                        }
                    }
                } elseif ($request->metodo_pago === 'bizum') {
                    $mensajeFinal = 'Registro recibido. Envia el Bizum al 600 000 000 con tu DNI como concepto para activar tu cuenta.';
                } elseif ($request->metodo_pago === 'paypal') {
                    $mensajeFinal = 'Registro recibido. Te hemos enviado un enlace de PayPal a ' . $user->email . ' para completar el pago.';
                }

                // Se registra el uso del cupon aunque el cobro sea pendiente/manual.
                if ($discountCode) {
                    $discountCode->markUsed($user, 'registro', $descuentoAplicado);
                }

                return response()->json([
                    'mensaje' => $mensajeFinal,
                    'descuento' => $mensajeDescuento,
                    'usuario_id' => $user->id,
                    'email' => $user->email,
                ], 201);
            });
        } catch (\Throwable $e) {
            // Error global no esperado.
            Log::error('Error en Registro SeaFit: ' . $e->getMessage());

            return response()->json([
                'error' => 'No se pudo completar el registro',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene el price_id de Stripe segun la tarifa elegida.
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
     * Calcula la fecha del proximo cobro segun la tarifa.
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
}

<?php

/**
 * Controlador de registro de socios.
 * Valida datos, crea usuarios y procesa el pago inicial.
 */
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegistroController extends Controller
{
    /**
     * Registra un nuevo socio y, segun el metodo de pago, activa suscripcion.
     */
    public function registrar(Request $request)
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
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
                ],
                'domicilio' => 'required|string|max:255',
                'tarifa' => 'required|string',
                'metodo_pago' => 'required|string',
                'cupon' => 'nullable|string',
                'stripeCodigo' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Errores de validacion',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (in_array($request->metodo_pago, ['visa', 'amex'], true) && !$request->filled('stripeCodigo')) {
                return response()->json([
                    'error' => 'Falta el metodo de pago de Stripe.',
                ], 422);
            }


            return DB::transaction(function () use ($request) {
                // Cupones disponibles y su descuento.
                $cuponesDisponibles = [
                    'SEAFIT20' => 0.20,
                    'BIENVENIDA' => 5.00
                ];

                // Informativo por ahora: no modifica importe en servidor.
                $mensajeDescuento = '';
                if ($request->filled('cupon') && isset($cuponesDisponibles[$request->cupon])) {
                    $mensajeDescuento = '¡Cupón aplicado con éxito!';
                }

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
                $user->payment_status = $esTarjeta ? 'al_dia' : 'pendiente';
                $user->next_payment_at = $esTarjeta ? $this->proximoCobroDesdeTarifa($request->tarifa) : null;

                $user->save();

                $mensajeFinal = '¡Socio registrado con éxito!';

                // Flujo de pago segun metodo seleccionado.
                if (in_array($request->metodo_pago, ['visa', 'amex'], true) && $request->filled('stripeCodigo')) {
                    $priceId = $this->priceIdDesdeTarifa($request->tarifa);

                    if ($priceId) {
                        // stripeCodigo viene como PaymentMethod ID.
                        try {
                            $user->createAsStripeCustomer();
                            $user->newSubscription('default', $priceId)->create($request->stripeCodigo);

                            $user->payment_status = 'al_dia';
                            $user->next_payment_at = $this->proximoCobroDesdeTarifa($request->tarifa);
                            $user->save();

                            $mensajeFinal = '¡Socio registrado y suscripcion activada con éxito!';
                        } catch (\Throwable $e) {
                            $user->payment_status = 'pendiente';
                            $user->next_payment_at = null;
                            $user->save();

                            $mensajeFinal = 'Registro creado, pero el pago con tarjeta no se pudo confirmar. Revisa tu metodo de pago.';
                        }

                    }
                } elseif ($request->metodo_pago === 'bizum') {
                    $mensajeFinal = '¡Registro recibido! Envía el Bizum al 600 000 000 con tu DNI como concepto para activar tu cuenta.';
                } elseif ($request->metodo_pago === 'paypal') {
                    $mensajeFinal = '¡Registro recibido! Te hemos enviado un enlace de PayPal a ' . $user->email . ' para completar el pago.';
                }

                return response()->json([
                    'mensaje' => $mensajeFinal,
                    'descuento' => $mensajeDescuento,
                    'usuario_id' => $user->id,
                    'email' => $user->email,
                ], 201);
            });
        } catch (\Exception $e) {
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
    private function priceIdDesdeTarifa(string $tarifa): ?string
    {
        return match ($tarifa) {
            'mensual' => 'price_1TEXJALV86wly52BTx1BvxYJ',
            'trimestral' => 'price_1TEXLwLV86wly52BLC3ihcbo',
            'anual' => 'price_1TEXLNLV86wly52BXUvZyG9R',
            default => null,
        };
    }

    /**
     * Calcula la fecha del proximo cobro segun la tarifa.
     */
    private function proximoCobroDesdeTarifa(string $tarifa): ?string
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


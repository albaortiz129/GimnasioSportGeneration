<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RegistroController extends Controller
{
    public function registrar(Request $request)
    {
        try {
            // 1. Validar los datos recibidos
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'dni' => 'required|string|unique:users,dni',
                'fecha_nacimiento' => 'required|date',
                'telefono' => 'required|string|max:20',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'domicilio' => 'required|string|max:255',
                'tarifa' => 'required|string',
                'metodo_pago' => 'required|string',
                'cupon' => 'nullable|string',
                'stripeToken' => 'required_if:metodo_pago,visa,amex'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            return DB::transaction(function () use ($request) {

                // --- LÓGICA DE DESCUENTOS ---
                $cuponIntroducido = $request->cupon;
                $mensajeDescuento = "";
                $cuponesDisponibles = [
                    'SEAFIT20' => 0.20,
                    'BIENVENIDA' => 5.00
                ];

                if ($cuponIntroducido && isset($cuponesDisponibles[$cuponIntroducido])) {
                    $mensajeDescuento = "¡Cupón aplicado con éxito!";
                }

                // 2. Crear el usuario
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
                $user->save();

                $mensajeFinal = '¡Socio registrado con éxito!';

                // 3. LÓGICA DE PAGOS HÍBRIDA
                if (($request->metodo_pago === 'visa' || $request->metodo_pago === 'amex') && $request->stripeToken) {
                    $priceId = match ($request->tarifa) {
                        'mensual' => 'price_1TEXJALV86wly52BTx1BvxYJ',
                        'trimestral' => 'price_1TEXLwLV86wly52BLC3ihcbo',
                        'anual' => 'price_1TEXLNLV86wly52BXUvZyG9R',
                        default => null
                    };

                    // En RegistroController.php, dentro del bloque de Stripe:
                    if ($priceId && $request->stripeToken) {
                        $user->createAsStripeCustomer();

                        // Usamos el ID de Payment Method (pm_...)
                        $user->newSubscription('default', $priceId)->create($request->stripeToken);

                        $mensajeFinal = '¡Socio registrado y suscripción activada con éxito!';
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
                    'email' => $user->email
                ], 201);
            });

        } catch (\Exception $e) {
            Log::error('Error en Registro SeaFit: ' . $e->getMessage());
            return response()->json([
                'error' => 'No se pudo completar el registro',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
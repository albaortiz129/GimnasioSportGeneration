<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagoController extends Controller
{
    /**
     * 1. Panel de gestión de pagos y suscripción
     */
    public function index()
    {
        $user = Auth::user();

        // Obtenemos la suscripción de Stripe
        $suscripcion = $user->subscription('default');

        // Obtenemos los métodos de pago (tarjetas) guardados en Stripe
        $metodosPago = $user->paymentMethods();
        $metodoPrincipal = $user->defaultPaymentMethod();

        return view('usuario.pago', compact('user', 'suscripcion', 'metodosPago', 'metodoPrincipal'));
    }

    /**
     * 2. Cancelar la suscripción (Mantiene el acceso hasta el fin del periodo)
     */
    public function cancelarPlan()
    {
        $user = Auth::user();

        if ($user->subscribed('default')) {
            // Le dice a Stripe que no renueve al final del ciclo
            $user->subscription('default')->cancel();

            // Marcamos localmente como cancelada para la lógica visual
            $user->update(['tarifa' => 'cancelada']);

            return back()->with('success', 'Tu suscripción ha sido cancelada. Tendrás acceso hasta el final del periodo pagado.');
        }

        return back()->with('error', 'No tienes una suscripción activa.');
    }

    /**
     * 3. Reanudar o Cambiar Plan (Llamado desde el Modal del Perfil)
     */
    public function reanudarPlan(Request $request)
    {
        $user = Auth::user();
        $nuevaTarifa = $request->input('tarifa', 'mensual');
        $coupon = $request->input('coupon'); // Recogemos el código

        $planId = match ($nuevaTarifa) {
            'mensual' => 'price_1TEXJALV86wly52BTx1BvxYJ',
            'trimestral' => 'price_1TEXLwLV86wly52BLC3ihcbo',
            'anual' => 'price_1TEXLNLV86wly52BXUvZyG9R',
            default => 'price_1TEXJALV86wly52BTx1BvxYJ'
        };

        try {
            $subscription = $user->subscription('default');

            if ($subscription) {
                // Si el usuario cambia de plan
                if ($user->tarifa !== $nuevaTarifa) {
                    $subscription->swap($planId);
                }

                // Si reanuda, aplicamos el cupón si existe
                if ($subscription->onGracePeriod()) {
                    if ($coupon) {
                        $subscription->applyCoupon($coupon);
                    }
                    $subscription->resume();
                }
            } else {
                // Nueva suscripción con cupón
                if (!$user->hasDefaultPaymentMethod()) {
                    return back()->with('error', 'No tienes una tarjeta guardada.');
                }

                $newSub = $user->newSubscription('default', $planId);

                if ($coupon) {
                    $newSub->withCoupon($coupon);
                }

                $newSub->create($user->defaultPaymentMethod()->id);
            }

            $user->tarifa = $nuevaTarifa;
            $user->save();

            return back()->with('success', '¡Plan activado! ' . ($coupon ? 'Descuento aplicado correctamente.' : ''));

        } catch (\Exception $e) {
            // Si el cupón no es válido, Stripe lanzará una excepción
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    /**
     * 4. Establecer método de pago principal en Stripe
     */
    public function establecerPrincipal(Request $request)
    {
        $user = Auth::user();

        // 1. Recogemos el ID. Asegúrate que en tu <select> o <input> el name sea "payment_method"
        $paymentMethodId = $request->input('payment_method');

        // 2. Si no llega nada, volvemos atrás con un aviso en lugar de dar error 500
        if (!$paymentMethodId) {
            return back()->with('error', 'No se seleccionó ningún método de pago.');
        }

        try {
            // 3. Actualizamos en Stripe
            $user->updateDefaultPaymentMethod($paymentMethodId);
            return back()->with('success', 'Método de pago principal actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar en Stripe: ' . $e->getMessage());
        }
    }

    /**
     * 5. Eliminar método de pago de Stripe
     */
    public function eliminarMetodo(Request $request)
    {
        $user = Auth::user();
        $paymentMethodId = $request->payment_method;

        $paymentMethod = $user->findPaymentMethod($paymentMethodId);

        if ($paymentMethod) {
            $paymentMethod->delete();
            return back()->with('success', 'Tarjeta eliminada correctamente.');
        }

        return back()->with('error', 'No se pudo encontrar el método de pago.');
    }

    /**
     * 6. Descargar Factura (PDF real de Stripe)
     */
    public function descargarFactura($invoiceId)
    {
        return Auth::user()->downloadInvoice($invoiceId, [
            'vendor' => 'SeaFit Gym',
            'product' => 'Membresía SeaFit',
        ]);
    }

    /**
     * Muestra el formulario para añadir una tarjeta nueva
     */
    public function nuevoMetodo()
    {
        $user = Auth::user();

        // Generamos un "SetupIntent". 
        // Esto es un permiso que le pedimos a Stripe para guardar una tarjeta sin cobrar nada ahora.
        return view('usuario.nuevo-metodo', [
            'intent' => $user->createSetupIntent()
        ]);
    }

    /**
     * Guarda la nueva tarjeta en Stripe tras recibir el token de la vista
     */
    public function guardarMetodo(Request $request)
    {
        $user = Auth::user();
        $paymentMethodId = $request->payment_method;

        try {
            $user->addPaymentMethod($paymentMethodId);

            // Si es la primera tarjeta que añade, la ponemos como principal
            if (!$user->hasDefaultPaymentMethod()) {
                $user->updateDefaultPaymentMethod($paymentMethodId);
            }

            return redirect()->route('pago.gestion')->with('success', '¡Nueva tarjeta añadida con éxito!');
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo guardar la tarjeta: ' . $e->getMessage());
        }
    }
}
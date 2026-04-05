<?php

/**
 *Controlador de pagos y suscripciones: integra Stripe/Cashier para metodos, facturas y planes.
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagoController extends Controller
{
    /**
     * Relacion entre cada tarifa y su precio de Stripe.
     */
    private const PRICE_IDS = [
        'mensual' => 'price_1TEXJALV86wly52BTx1BvxYJ',
        'trimestral' => 'price_1TEXLwLV86wly52BLC3ihcbo',
        'anual' => 'price_1TEXLNLV86wly52BXUvZyG9R',
    ];

    /**
     * Muestra la pantalla de pagos y suscripcion del usuario.
     */
    public function index()
    {
        $user = Auth::user();

        // Datos de pago y suscripcion obtenidos desde Stripe.
        $suscripcion = $user->subscription('default');
        $metodosPago = $user->paymentMethods();
        $metodoPrincipal = $user->defaultPaymentMethod();

        return view('usuario.pago', compact('user', 'suscripcion', 'metodosPago', 'metodoPrincipal'));
    }

    /**
     * Cancela la renovacion automatica al final del periodo actual.
     */
    public function cancelarPlan()
    {
        $user = Auth::user();

        if (!$user->subscribed('default')) {
            return back()->with('error', 'No tienes una suscripcion activa.');
        }

        // Se cancela en Stripe pero mantiene acceso hasta fin de ciclo.
        $user->subscription('default')->cancel();

        // Dato guardado para que la web muestre el plan como cancelado.
        $user->update(['tarifa' => 'cancelada']);

        return back()->with('success', 'Tu suscripcion ha sido cancelada. Tendrás acceso hasta el final del periodo pagado.');
    }

    /**
     * Reanuda suscripcion en periodo de gracia o crea una nueva.
     * Tambien permite cambiar de plan activo.
     */
    public function reanudarPlan(Request $request)
    {
        $request->validate([
            'tarifa' => 'nullable|in:mensual,trimestral,anual',
            'coupon' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $nuevaTarifa = $request->input('tarifa', 'mensual');
        $coupon = $request->input('coupon');
        $planId = $this->priceIdDesdeTarifa($nuevaTarifa);

        try {
            $subscription = $user->subscription('default');

            if ($subscription) {
                // Si ya tiene plan y cambia de tarifa, cambiamos al nuevo plan.
                if ($user->tarifa !== $nuevaTarifa) {
                    $subscription->swap($planId);
                }

                // Si esta cancelada pero aun activa hasta fin de ciclo, la reactivamos.
                if ($subscription->onGracePeriod()) {
                    if ($coupon) {
                        $subscription->applyCoupon($coupon);
                    }

                    $subscription->resume();
                }
            } else {
                // Alta de nueva suscripcion.
                if (!$user->hasDefaultPaymentMethod()) {
                    return back()->with('error', 'No tienes una tarjeta guardada.');
                }

                $newSubscription = $user->newSubscription('default', $planId);

                if ($coupon) {
                    $newSubscription->withCoupon($coupon);
                }

                $newSubscription->create($user->defaultPaymentMethod()->id);
            }

            // Sincronizamos la tarifa visible en la cuenta local.
            $user->update(['tarifa' => $nuevaTarifa]);

            return back()->with('success', '¡Plan activado! ' . ($coupon ? 'Descuento aplicado correctamente.' : ''));
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Define una tarjeta guardada como metodo principal.
     */
    public function establecerPrincipal(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $user = Auth::user();
        $paymentMethodId = $request->input('payment_method');

        try {
            $user->updateDefaultPaymentMethod($paymentMethodId);

            return back()->with('success', 'Método de pago principal actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar en Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una tarjeta guardada en Stripe.
     */
    public function eliminarMetodo(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $user = Auth::user();
        $paymentMethodId = $request->input('payment_method');

        $paymentMethod = $user->findPaymentMethod($paymentMethodId);

        if (!$paymentMethod) {
            return back()->with('error', 'No se pudo encontrar el método de pago.');
        }

        $paymentMethod->delete();

        return back()->with('success', 'Tarjeta eliminada correctamente.');
    }

    /**
     * Descarga un PDF de factura generado por Stripe.
     */
    public function descargarFactura($invoiceId)
    {
        return Auth::user()->downloadInvoice($invoiceId, [
            'vendor' => 'SeaFit Gym',
            'product' => 'Membresía SeaFit',
        ]);
    }

    /**
     * Pantalla para añadir una tarjeta usando Setup Intent.
     */
    public function nuevoMetodo()
    {
        $user = Auth::user();

        return view('usuario.nuevo-metodo', [
            'intent' => $user->createSetupIntent(),
        ]);
    }

    /**
     * Guarda una nueva tarjeta en Stripe desde su payment method ID.
     */
    public function guardarMetodo(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $user = Auth::user();
        $paymentMethodId = $request->input('payment_method');

        try {
            $user->addPaymentMethod($paymentMethodId);

            // Si es la primera tarjeta, pasa a ser la principal.
            if (!$user->hasDefaultPaymentMethod()) {
                $user->updateDefaultPaymentMethod($paymentMethodId);
            }

            return redirect()->route('pago.gestion')->with('success', '¡Nueva tarjeta añadida con éxito!');
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo guardar la tarjeta: ' . $e->getMessage());
        }
    }

    /**
     * Devuelve el precio de Stripe segun la tarifa elegida.
     */
    private function priceIdDesdeTarifa(string $tarifa): string
    {
        return self::PRICE_IDS[$tarifa] ?? self::PRICE_IDS['mensual'];
    }
}


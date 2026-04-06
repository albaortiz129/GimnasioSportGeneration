<?php

/**
 * Controlador de pagos y suscripciones del socio.
 * Gestiona tarjetas, metodos manuales, facturas y cambios de plan.
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagoController extends Controller
{
    /**
     * Mapa de tarifa interna a su precio de Stripe.
     */
    private const PRICE_IDS = [
        'mensual' => 'price_1TEXJALV86wly52BTx1BvxYJ',
        'trimestral' => 'price_1TEXLwLV86wly52BLC3ihcbo',
        'anual' => 'price_1TEXLNLV86wly52BXUvZyG9R',
    ];

    /**
     * Carga la pantalla de gestion de pagos del usuario.
     */
    public function index()
    {
        $user = Auth::user();

        $metodosPago = $user->paymentMethods();
        $metodoPrincipal = $user->defaultPaymentMethod();

        $metodosManuales = collect($user->manual_payment_methods ?? [])
            ->map(fn($metodo) => $this->normalizarMetodoManual($metodo))
            ->filter()
            ->values();

        return view('usuario.pago', compact(
            'user',
            'metodosPago',
            'metodoPrincipal',
            'metodosManuales'
        ));
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

        return back()->with('success', 'Tu suscripcion ha sido cancelada. Tendras acceso hasta el final del periodo pagado.');
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

            return back()->with('success', 'Plan activado! ' . ($coupon ? 'Descuento aplicado correctamente.' : ''));
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
            $metodoStripe = $user->findPaymentMethod($paymentMethodId);
            $marca = strtolower((string) optional($metodoStripe->card ?? null)->brand);

            $user->update([
                'metodo_pago' => $this->metodoPagoDesdeMarca($marca),
            ]);

            return back()->with('success', 'Metodo de pago principal actualizado correctamente.');
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
            return back()->with('error', 'No se pudo encontrar el metodo de pago.');
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
            'product' => 'Membresia SeaFit',
        ]);
    }

    /**
     * Pantalla para anadir una tarjeta usando Setup Intent.
     */
    public function nuevoMetodo()
    {
        $user = Auth::user();

        if (!$user->stripe_id) {
            $user->createAsStripeCustomer();
        }

        return view('usuario.nuevo-metodo', [
            'intent' => $user->createSetupIntent(),
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    public function guardarMetodo(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $user = Auth::user();

        if (!$user->stripe_id) {
            $user->createAsStripeCustomer();
        }

        try {
            $user->addPaymentMethod($request->payment_method);

            if (!$user->hasDefaultPaymentMethod()) {
                $user->updateDefaultPaymentMethod($request->payment_method);
            }

            return redirect()->route('pago.gestion')->with('success', 'Tarjeta anadida correctamente.');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo guardar la tarjeta: ' . $e->getMessage());
        }
    }

    /**
     * Guarda o actualiza un metodo manual (Bizum/PayPal) y su dato.
     */
    public function guardarMetodoManual(Request $request)
    {
        $data = $request->validate([
            'metodo_manual' => 'required|in:bizum,paypal',
            'dato_manual' => 'required|string|max:120',
        ], [
            'metodo_manual.required' => 'Selecciona un metodo manual.',
            'metodo_manual.in' => 'Metodo manual no valido.',
            'dato_manual.required' => 'Debes indicar los datos del metodo.',
            'dato_manual.max' => 'El dato es demasiado largo.',
        ]);

        if ($data['metodo_manual'] === 'bizum') {
            $request->validate([
                'dato_manual' => 'regex:/^[6789]\d{8}$/',
            ], [
                'dato_manual.regex' => 'El telefono de Bizum debe tener 9 digitos y empezar por 6, 7, 8 o 9.',
            ]);
        }

        if ($data['metodo_manual'] === 'paypal') {
            $request->validate([
                'dato_manual' => 'email:rfc',
            ], [
                'dato_manual.email' => 'El email de PayPal no es valido.',
            ]);
        }

        $user = Auth::user();
        $manuales = collect($user->manual_payment_methods ?? [])
            ->map(fn($metodo) => $this->normalizarMetodoManual($metodo))
            ->filter()
            ->values();

        $datoManual = trim($data['dato_manual']);

        if ($data['metodo_manual'] === 'bizum') {
            $datoManual = preg_replace('/\D+/', '', $datoManual);
        }

        if ($data['metodo_manual'] === 'paypal') {
            $datoManual = strtolower($datoManual);
        }

        $yaExistia = $manuales->contains(fn($metodo) => $metodo['code'] === $data['metodo_manual']);

        $user->manual_payment_methods = $manuales
            ->reject(fn($metodo) => $metodo['code'] === $data['metodo_manual'])
            ->push([
                'code' => $data['metodo_manual'],
                'value' => $datoManual,
            ])
            ->values()
            ->all();

        if (!in_array($user->metodo_pago, ['bizum', 'paypal'], true)) {
            $user->metodo_pago = $data['metodo_manual'];
        }

        $user->save();

        return back()->with('success', $yaExistia ? 'Metodo manual actualizado.' : 'Metodo manual guardado.');
    }

    /**
     * Pone un metodo manual como metodo principal.
     */
    public function principalManual(Request $request)
    {
        $data = $request->validate([
            'metodo_manual' => 'required|in:bizum,paypal',
        ]);

        $user = Auth::user();
        $manuales = collect($user->manual_payment_methods ?? [])
            ->map(fn($metodo) => $this->normalizarMetodoManual($metodo))
            ->filter()
            ->values();

        if (!$manuales->contains(fn($metodo) => $metodo['code'] === $data['metodo_manual'])) {
            return back()->with('error', 'Metodo no encontrado.');
        }

        $user->update([
            'metodo_pago' => $data['metodo_manual'],
        ]);

        return back()->with('success', 'Metodo principal actualizado.');
    }

    /**
     * Elimina un metodo manual guardado.
     */
    public function eliminarMetodoManual(Request $request)
    {
        $data = $request->validate([
            'metodo_manual' => 'required|in:bizum,paypal',
        ]);

        $user = Auth::user();
        $manuales = collect($user->manual_payment_methods ?? [])
            ->map(fn($metodo) => $this->normalizarMetodoManual($metodo))
            ->filter()
            ->reject(fn($metodo) => $metodo['code'] === $data['metodo_manual'])
            ->values();

        $user->manual_payment_methods = $manuales
            ->map(fn($metodo) => [
                'code' => $metodo['code'],
                'value' => $metodo['value'],
            ])
            ->values()
            ->all();

        if ($user->metodo_pago === $data['metodo_manual']) {
            if ($manuales->isNotEmpty()) {
                $user->metodo_pago = $manuales->first()['code'];
            } elseif ($user->hasDefaultPaymentMethod()) {
                $brand = strtolower((string) optional($user->defaultPaymentMethod()->card)->brand);
                $user->metodo_pago = $brand ?: 'visa';
            }
        }

        $user->save();

        return back()->with('success', 'Metodo manual eliminado.');
    }

    /**
     * Convierte formatos antiguos y nuevos al mismo formato de salida.
     */
    private function normalizarMetodoManual(mixed $metodo): ?array
    {
        if (is_string($metodo)) {
            $code = strtolower(trim($metodo));

            if (!in_array($code, ['bizum', 'paypal'], true)) {
                return null;
            }

            return [
                'code' => $code,
                'label' => $this->nombreMetodoManual($code),
                'value' => null,
                'value_masked' => null,
            ];
        }

        if (is_array($metodo)) {
            $code = strtolower(trim((string) ($metodo['code'] ?? '')));

            if (!in_array($code, ['bizum', 'paypal'], true)) {
                return null;
            }

            $value = trim((string) ($metodo['value'] ?? ''));
            $value = $value === '' ? null : $value;

            return [
                'code' => $code,
                'label' => $this->nombreMetodoManual($code),
                'value' => $value,
                'value_masked' => $this->enmascararDatoManual($code, $value),
            ];
        }

        return null;
    }

    /**
     * Enmascara el dato manual para no mostrarlo completo.
     */
    private function enmascararDatoManual(string $code, ?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return match ($code) {
            'bizum' => $this->enmascararTelefono($value),
            'paypal' => $this->enmascararEmail($value),
            default => $value,
        };
    }

    /**
     * Enmascara telefono de Bizum.
     */
    private function enmascararTelefono(string $telefono): string
    {
        $soloDigitos = preg_replace('/\D+/', '', $telefono);

        if (strlen($soloDigitos) <= 3) {
            return $soloDigitos;
        }

        return str_repeat('*', strlen($soloDigitos) - 3) . substr($soloDigitos, -3);
    }

    /**
     * Enmascara email de PayPal.
     */
    private function enmascararEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return $email;
        }

        [$usuario, $dominio] = explode('@', $email, 2);

        if ($usuario === '') {
            return '*@' . $dominio;
        }

        return substr($usuario, 0, 1) . str_repeat('*', max(strlen($usuario) - 1, 1)) . '@' . $dominio;
    }

    /**
     * Devuelve el nombre visible de un metodo manual.
     */
    private function nombreMetodoManual(string $code): string
    {
        return match ($code) {
            'bizum' => 'Bizum',
            'paypal' => 'PayPal',
            default => ucfirst($code),
        };
    }

    /**
     * Convierte la marca de Stripe al valor interno de metodo_pago.
     */
    private function metodoPagoDesdeMarca(?string $marca): string
    {
        return match ($marca) {
            'visa' => 'visa',
            'amex', 'american express' => 'amex',
            default => 'tarjeta',
        };
    }


    /**
     * Devuelve el precio de Stripe segun la tarifa elegida.
     */
    private function priceIdDesdeTarifa(string $tarifa): string
    {
        return self::PRICE_IDS[$tarifa] ?? self::PRICE_IDS['mensual'];
    }

    /**
     * Cambia tarifa y metodo de pago desde el panel de socio.
     */
    public function cambiarPlanMetodo(Request $request)
    {
        $request->validate([
            'tarifa' => 'required|in:mensual,trimestral,anual',
            'metodo_pago' => 'required|in:visa,amex,bizum,paypal,transferencia,efectivo',
        ]);

        $user = Auth::user();
        $tarifa = $request->tarifa;
        $metodo = $request->metodo_pago;

        if (in_array($metodo, ['visa', 'amex'], true)) {
            if (!$user->hasDefaultPaymentMethod()) {
                return back()->with('error', 'Para pagar con tarjeta, primero anade una tarjeta.');
            }

            $planId = $this->priceIdDesdeTarifa($tarifa);

            try {
                $subscription = $user->subscription('default');

                if ($subscription) {
                    $subscription->swap($planId);
                } else {
                    $user->newSubscription('default', $planId)
                        ->create($user->defaultPaymentMethod()->id);
                }

                $user->update([
                    'tarifa' => $tarifa,
                    'metodo_pago' => $metodo,
                    'payment_status' => 'al_dia',
                    'next_payment_at' => $this->proximoCobroDesdeTarifa($tarifa),
                ]);

                return back()->with('success', 'Plan y metodo actualizados correctamente.');
            } catch (\Throwable $e) {
                return back()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
            }
        }

        // Metodos manuales
        $user->update([
            'tarifa' => $tarifa,
            'metodo_pago' => $metodo,
            'payment_status' => 'pendiente',
            'next_payment_at' => null,
        ]);

        return back()->with('success', 'Cambio solicitado. El admin debe validar el pago manual.');
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



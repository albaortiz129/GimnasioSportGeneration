<?php

/**
 * Controlador de pagos y suscripciones del socio.
 * Gestiona tarjetas, métodos manuales, facturas y cambios de plan.
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DiscountCode;


class PaymentController extends Controller
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
     * Carga la pantalla de gestión de pagos del usuario.
     */
    public function index()
    {
        $user = Auth::user();

        // Metodos guardados en Stripe.
        $metodosPago = $user->paymentMethods();
        $metodoPrincipal = $user->defaultPaymentMethod();

        // Metodos manuales guardados en la BD (Bizum/PayPal).
        $metodosManuales = collect($user->manual_payment_methods ?? [])
            ->map(fn($metodo) => $this->normalizeManualMethod($metodo))
            ->filter()
            ->values();

        return view('user.payment', compact(
            'user',
            'metodosPago',
            'metodoPrincipal',
            'metodosManuales'
        ));
    }


    /**
     * Cancela la renovación automática al final del periodo actual.
     */
    public function cancelPlan()
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
     * Reanuda suscripción o crea una nueva.
     * También permite cambiar de plan activo.
     */
    public function resumePlan(Request $request)
    {
        $request->validate([
            'tarifa' => 'nullable|in:mensual,trimestral,anual',
            'cupon' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $nuevaTarifa = $request->input('tarifa', 'mensual');
        $codigoCupon = $request->input('cupon');

        // Valida cupon solo si el usuario ha escrito uno.
        $cupon = $this->resolveStripeCoupon($codigoCupon, $user, 'reanudar_plan');
        if ($cupon['error']) {
            return back()->with('error', $cupon['error']);
        }

        $planId = $this->priceIdFromPlan($nuevaTarifa);

        try {
            $subscription = $user->subscription('default');

            if ($subscription) {
                // Si ya existe suscripcion, se reutiliza y se ajusta plan/cupon.
                if ($user->tarifa !== $nuevaTarifa) {
                    $subscription->swap($planId);
                }

                if ($cupon['stripe_coupon_id']) {
                    $subscription->applyCoupon($cupon['stripe_coupon_id']);
                }

                if ($subscription->onGracePeriod()) {
                    $subscription->resume();
                }
            } else {
                // Si no existe, se crea desde cero con el metodo por defecto.
                if (!$user->hasDefaultPaymentMethod()) {
                    return back()->with('error', 'No tienes una tarjeta guardada.');
                }

                $newSubscription = $user->newSubscription('default', $planId);

                if ($cupon['stripe_coupon_id']) {
                    $newSubscription->withCoupon($cupon['stripe_coupon_id']);
                }

                $newSubscription->create($user->defaultPaymentMethod()->id);
            }

            $user->update(['tarifa' => $nuevaTarifa]);

            if ($cupon['model']) {
                $descuentoAplicado = $cupon['model']->calculateDiscountAmount($this->planBaseAmount($nuevaTarifa));
                $cupon['model']->markUsed($user, 'reanudar_plan', $descuentoAplicado);
            }

            return back()->with('success', 'Plan activado correctamente.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }


    /**
     * Define una tarjeta guardada como método principal.
     */
    public function setPrimaryMethod(Request $request)
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
                'metodo_pago' => $this->paymentMethodFromBrand($marca),
            ]);

            return back()->with('success', 'Metodo de pago principal actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar en Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una tarjeta guardada en Stripe.
     */
    public function deleteMethod(Request $request)
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
    public function downloadInvoice($invoiceId)
    {
        return Auth::user()->downloadInvoice($invoiceId, [
            'vendor' => 'SeaFit Gym',
            'product' => 'Membresia SeaFit',
        ]);
    }

    /**
     * Pantalla para añadir una tarjeta usando Setup Intent.
     */
    public function newMethod()
    {
        $user = Auth::user();

        // Si aun no es cliente de Stripe, se crea su perfil remoto.
        if (!$user->stripe_id) {
            $user->createAsStripeCustomer();
        }

        return view('user.new-payment-method', [
            'intent' => $user->createSetupIntent(),
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    /**
     * Guarda una tarjeta nueva en Stripe y, si hace falta, la pone como principal.
     */
    public function saveMethod(Request $request)
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
     * Guarda o actualiza un método manual (Bizum/PayPal) y su dato.
     */
    public function saveManualMethod(Request $request)
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

        // Reglas extra por tipo de metodo.
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
            ->map(fn($metodo) => $this->normalizeManualMethod($metodo))
            ->filter()
            ->values();

        $datoManual = trim($data['dato_manual']);

        if ($data['metodo_manual'] === 'bizum') {
            $datoManual = preg_replace('/\D+/', '', $datoManual);
        }

        if ($data['metodo_manual'] === 'paypal') {
            $datoManual = strtolower($datoManual);
        }

        // Detecta si era alta nueva o una actualizacion del metodo.
        $yaExistia = $manuales->contains(fn($metodo) => $metodo['code'] === $data['metodo_manual']);

        $user->manual_payment_methods = $manuales
            ->reject(fn($metodo) => $metodo['code'] === $data['metodo_manual'])
            ->push([
                'code' => $data['metodo_manual'],
                'value' => $datoManual,
            ])
            ->values()
            ->all();

        // Si el principal actual no es manual, pasa a ser el recien guardado.
        if (!in_array($user->metodo_pago, ['bizum', 'paypal'], true)) {
            $user->metodo_pago = $data['metodo_manual'];
        }

        $user->save();

        return back()->with('success', $yaExistia ? 'Metodo manual actualizado.' : 'Metodo manual guardado.');
    }

    /**
     * Pone un método manual como método principal.
     */
    public function setPrimaryManualMethod(Request $request)
    {
        $data = $request->validate([
            'metodo_manual' => 'required|in:bizum,paypal',
        ]);

        $user = Auth::user();
        $manuales = collect($user->manual_payment_methods ?? [])
            ->map(fn($metodo) => $this->normalizeManualMethod($metodo))
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
     * Elimina un método manual guardado.
     */
    public function deleteManualMethod(Request $request)
    {
        $data = $request->validate([
            'metodo_manual' => 'required|in:bizum,paypal',
        ]);

        $user = Auth::user();
        $manuales = collect($user->manual_payment_methods ?? [])
            ->map(fn($metodo) => $this->normalizeManualMethod($metodo))
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

        // Si se borra el principal, se intenta seleccionar otro automaticamente.
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
    private function normalizeManualMethod(mixed $metodo): ?array
    {
        if (is_string($metodo)) {
            $code = strtolower(trim($metodo));

            if (!in_array($code, ['bizum', 'paypal'], true)) {
                return null;
            }

            return [
                'code' => $code,
                'label' => $this->manualMethodName($code),
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
                'label' => $this->manualMethodName($code),
                'value' => $value,
                'value_masked' => $this->maskManualData($code, $value),
            ];
        }

        return null;
    }

    /**
     * Enmascara el dato manual para no mostrarlo completo.
     */
    private function maskManualData(string $code, ?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return match ($code) {
            'bizum' => $this->maskPhone($value),
            'paypal' => $this->maskEmail($value),
            default => $value,
        };
    }

    /**
     * Cuber telefono de Bizum.
     */
    private function maskPhone(string $telefono): string
    {
        $soloDigitos = preg_replace('/\D+/', '', $telefono);

        if (strlen($soloDigitos) <= 3) {
            return $soloDigitos;
        }

        return str_repeat('*', strlen($soloDigitos) - 3) . substr($soloDigitos, -3);
    }

    /**
     * Cubre email de PayPal.
     */
    private function maskEmail(string $email): string
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
     * Devuelve el nombre visible de un método manual.
     */
    private function manualMethodName(string $code): string
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
    private function paymentMethodFromBrand(?string $marca): string
    {
        return match ($marca) {
            'visa' => 'visa',
            'amex', 'american express' => 'amex',
            default => 'tarjeta',
        };
    }


    /**
     * Devuelve el precio de Stripe según la tarifa elegida.
     */
    private function priceIdFromPlan(string $tarifa): string
    {
        return self::PRICE_IDS[$tarifa] ?? self::PRICE_IDS['mensual'];
    }

    /**
     * Importe base del plan antes de aplicar descuentos.
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
     * Cambia tarifa y método de pago desde el panel de socio.
     */
    public function changePlanMethod(Request $request)
    {
        $request->validate([
            'tarifa' => 'required|in:mensual,trimestral,anual',
            'metodo_pago' => 'required|in:visa,amex,bizum,paypal,transferencia,efectivo',
            'cupon' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $tarifa = $request->tarifa;
        $metodo = $request->metodo_pago;
        $codigoCupon = $request->input('cupon');

        // Flujo de pago con tarjeta (Stripe).
        if (in_array($metodo, ['visa', 'amex'], true)) {
            if (!$user->hasDefaultPaymentMethod()) {
                return back()->with('error', 'Para pagar con tarjeta, primero anade una tarjeta.');
            }

            $cupon = $this->resolveStripeCoupon($codigoCupon, $user, 'cambio_plan');
            if ($cupon['error']) {
                return back()->with('error', $cupon['error']);
            }

            $planId = $this->priceIdFromPlan($tarifa);

            try {
                $subscription = $user->subscription('default');

                if ($subscription) {
                    $subscription->swap($planId);

                    if ($cupon['stripe_coupon_id']) {
                        $subscription->applyCoupon($cupon['stripe_coupon_id']);
                    }
                } else {
                    $newSubscription = $user->newSubscription('default', $planId);

                    if ($cupon['stripe_coupon_id']) {
                        $newSubscription->withCoupon($cupon['stripe_coupon_id']);
                    }

                    $newSubscription->create($user->defaultPaymentMethod()->id);
                }

                $user->update([
                    'tarifa' => $tarifa,
                    'metodo_pago' => $metodo,
                    'payment_status' => 'al_dia',
                    'next_payment_at' => $this->nextChargeFromPlan($tarifa),
                ]);

                if ($cupon['model']) {
                    $descuentoAplicado = $cupon['model']->calculateDiscountAmount($this->planBaseAmount($tarifa));
                    $cupon['model']->markUsed($user, 'cambio_plan', $descuentoAplicado);
                }

                return back()->with('success', 'Plan y metodo actualizados correctamente.');
            } catch (\Throwable $e) {
                return back()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
            }
        }

        // Los cupones solo se aceptan cuando se cobra por Stripe.
        if (!empty($codigoCupon)) {
            return back()->with('error', 'El cupon solo se aplica a pagos con tarjeta Stripe.');
        }

        $user->update([
            'tarifa' => $tarifa,
            'metodo_pago' => $metodo,
            'payment_status' => 'pendiente',
            'next_payment_at' => null,
        ]);

        return back()->with('success', 'Cambio solicitado. El admin debe validar el pago manual.');
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
     * Resuelve y valida un cupon para Stripe.
     * Devuelve:
     * - model: modelo DiscountCode o null
     * - stripe_coupon_id: id de Stripe o null
     * - error: mensaje de error o null
     */
    private function resolveStripeCoupon(?string $codigo, $user, string $context): array
    {
        $codigo = strtoupper(trim((string) $codigo));

        if ($codigo === '') {
            return [
                'model' => null,
                'stripe_coupon_id' => null,
                'error' => null,
            ];
        }

        $discount = DiscountCode::byCode($codigo)->first();

        if (!$discount) {
            return [
                'model' => null,
                'stripe_coupon_id' => null,
                'error' => 'El cupon no existe.',
            ];
        }

        if (!$discount->canBeUsedBy($user, $context)) {
            return [
                'model' => null,
                'stripe_coupon_id' => null,
                'error' => 'El cupon no esta activo, esta caducado o ya fue usado.',
            ];
        }

        if (empty($discount->stripe_coupon_id)) {
            return [
                'model' => null,
                'stripe_coupon_id' => null,
                'error' => 'El cupon no esta vinculado a Stripe.',
            ];
        }

        return [
            'model' => $discount,
            'stripe_coupon_id' => $discount->stripe_coupon_id,
            'error' => null,
        ];
    }

}

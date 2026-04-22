<?php

/**
 * Controlador de pagos y suscripciones del socio.
 * Gestiona tarjetas, métodos manuales, facturas y cambios de plan.
 */
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DiscountCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


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

        // Métodos guardados en Stripe.
        $metodosPago = $user->paymentMethods();
        $metodoPrincipal = $user->defaultPaymentMethod();

        // Métodos manuales guardados en la BD.
        // Bizum/PayPal se conservan solo para compatibilidad con datos antiguos
        // y por si se reactivan en el futuro.
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
     * Cancela la renovación automática al final del período actual.
     */
    public function cancelPlan()
    {
        $user = Auth::user();

        $suscripcion = $user->subscription('default');

        // Flujo para suscripciones de Stripe (tarjeta).
        if ($suscripcion && $user->subscribed('default')) {
            if ($suscripcion->onGracePeriod()) {
                return back()->with('success', 'Tu suscripción ya estaba programada para cancelarse al final del período.');
            }

            // Se cancela en Stripe pero mantiene acceso hasta fin de ciclo.
            $suscripcion->cancel();

            return back()->with('success', 'Tu suscripción ha sido cancelada. Tendrás acceso hasta el final del período pagado.');
        }

        // Flujo para pagos manuales (actualmente efectivo/transferencia).
        // Bizum/PayPal quedan fuera, pero pueden recuperarse en el futuro.
        if ($user->tarifa === 'cancelada') {
            return back()->with('success', 'Tu cancelación ya estaba programada para el final del período actual.');
        }

        if (!$user->isPlanActive()) {
            return back()->with('error', 'No tienes una suscripción activa para cancelar.');
        }

        $fechaFin = $user->next_payment_at
            ? Carbon::parse($user->next_payment_at)->toDateString()
            : $this->nextChargeFromPlan((string) $user->tarifa);

        $user->update([
            'tarifa' => 'cancelada',
            'payment_status' => 'al_dia',
            'next_payment_at' => $fechaFin,
        ]);

        $fechaVisible = $fechaFin ? Carbon::parse($fechaFin)->format('d/m/Y') : 'fin del período actual';

        return back()->with('success', "Tu suscripción se cancelará al final del período actual ({$fechaVisible}).");
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

        // Valida cupón solo si el usuario ha escrito uno.
        $cupon = $this->resolveStripeCoupon($codigoCupon, $user, 'reanudar_plan');
        if ($cupon['error']) {
            return back()->with('error', $cupon['error']);
        }

        $planId = $this->priceIdFromPlan($nuevaTarifa);

        try {
            $subscription = $user->subscription('default');

            if ($subscription) {
                // Si ya existe suscripción, se reutiliza y se ajusta plan/cupón.
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
                // Si no existe, se crea desde cero con el método por defecto.
                if (!$user->hasDefaultPaymentMethod()) {
                    return back()->with('error', 'No tienes una tarjeta guardada.');
                }

                $newSubscription = $user->newSubscription('default', $planId);

                if ($cupon['stripe_coupon_id']) {
                    $newSubscription->withCoupon($cupon['stripe_coupon_id']);
                }

                $newSubscription->create($user->defaultPaymentMethod()->id);
            }

            $user->update([
                'tarifa' => $nuevaTarifa,
                'payment_status' => 'al_dia',
                'next_payment_at' => $this->nextChargeFromPlan($nuevaTarifa),
            ]);

            if ($cupon['model']) {
                $descuentoAplicado = $cupon['model']->calculateDiscountAmount($this->planBaseAmount($nuevaTarifa));
                $cupon['model']->markUsed($user, 'reanudar_plan', $descuentoAplicado);
            }

            // Envia correo para confirmar que el pago con tarjeta esta aprobado.
            $this->sendPaymentApprovedEmail(
                $user,
                'Tarjeta',
                'Pago con tarjeta confirmado al activar o reanudar el plan'
            );

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

            return back()->with('success', 'Método de pago principal actualizado correctamente.');
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
            return back()->with('error', 'No se pudo encontrar el método de pago.');
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
            'product' => 'Membresía SeaFit',
        ]);
    }

    /**
     * Pantalla para añadir una tarjeta usando Setup Intent.
     */
    public function newMethod()
    {
        $user = Auth::user();

        // Si aún no es cliente de Stripe, se crea su perfil remoto.
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

            return redirect()->route('pago.gestion')->with('success', 'Tarjeta añadida correctamente.');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo guardar la tarjeta: ' . $e->getMessage());
        }
    }

    /**
     * Guarda o actualiza un método manual activo y su dato.
     */
    public function saveManualMethod(Request $request)
    {
        $data = $request->validate([
            // Bizum/PayPal desactivados temporalmente.
            'metodo_manual' => 'required|in:transferencia,efectivo',
            'dato_manual' => 'nullable|string|max:120',
        ], [
            'metodo_manual.required' => 'Selecciona un método manual.',
            'metodo_manual.in' => 'Método manual no válido.',
            'dato_manual.max' => 'El dato es demasiado largo.',
        ]);

        // Reglas extra por tipo de método.
        if ($data['metodo_manual'] === 'transferencia') {
            $request->validate([
                'dato_manual' => 'required|string|min:6|max:120',
            ], [
                'dato_manual.required' => 'Indica un dato para la transferencia (por ejemplo IBAN o referencia).',
                'dato_manual.min' => 'El dato de transferencia es demasiado corto.',
            ]);
        }

        $user = Auth::user();
        $manuales = collect($user->manual_payment_methods ?? [])
            ->map(fn($metodo) => $this->normalizeManualMethod($metodo))
            ->filter()
            ->values();

        $datoManual = trim((string) ($data['dato_manual'] ?? ''));

        if ($data['metodo_manual'] === 'efectivo') {
            $datoManual = null;
        }

        // Detecta si era alta nueva o una actualización del método.
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
        if (!in_array($user->metodo_pago, ['transferencia', 'efectivo', 'bizum', 'paypal'], true)) {
            $user->metodo_pago = $data['metodo_manual'];
        }

        $user->save();

        return back()->with('success', $yaExistia ? 'Método manual actualizado.' : 'Método manual guardado.');
    }

    /**
     * Pone un método manual como método principal.
     */
    public function setPrimaryManualMethod(Request $request)
    {
        $data = $request->validate([
            // Bizum/PayPal se mantienen aquí para poder gestionar datos antiguos.
            'metodo_manual' => 'required|in:transferencia,efectivo,bizum,paypal',
        ]);

        $user = Auth::user();
        $manuales = collect($user->manual_payment_methods ?? [])
            ->map(fn($metodo) => $this->normalizeManualMethod($metodo))
            ->filter()
            ->values();

        if (!$manuales->contains(fn($metodo) => $metodo['code'] === $data['metodo_manual'])) {
            return back()->with('error', 'Método no encontrado.');
        }

        $user->update([
            'metodo_pago' => $data['metodo_manual'],
        ]);

        return back()->with('success', 'Método principal actualizado.');
    }

    /**
     * Elimina un método manual guardado.
     */
    public function deleteManualMethod(Request $request)
    {
        $data = $request->validate([
            // Bizum/PayPal se mantienen aquí para poder eliminar datos antiguos.
            'metodo_manual' => 'required|in:transferencia,efectivo,bizum,paypal',
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

        // Si se borra el principal, se intenta seleccionar otro automáticamente.
        if ($user->metodo_pago === $data['metodo_manual']) {
            if ($manuales->isNotEmpty()) {
                $user->metodo_pago = $manuales->first()['code'];
            } elseif ($user->hasDefaultPaymentMethod()) {
                $brand = strtolower((string) optional($user->defaultPaymentMethod()->card)->brand);
                $user->metodo_pago = $brand ?: 'visa';
            }
        }

        $user->save();

        return back()->with('success', 'Método manual eliminado.');
    }

    /**
     * Convierte formatos antiguos y nuevos al mismo formato de salida.
     */
    private function normalizeManualMethod(mixed $metodo): ?array
    {
        if (is_string($metodo)) {
            $code = strtolower(trim($metodo));

            // Bizum/PayPal se aceptan solo para compatibilidad histórica.
            if (!in_array($code, ['transferencia', 'efectivo', 'bizum', 'paypal'], true)) {
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

            // Bizum/PayPal se aceptan solo para compatibilidad histórica.
            if (!in_array($code, ['transferencia', 'efectivo', 'bizum', 'paypal'], true)) {
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
            'transferencia' => $value,
            'bizum' => $this->maskPhone($value),
            'paypal' => $this->maskEmail($value),
            default => $value,
        };
    }

    /**
     * Cubre teléfono de Bizum.
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
            'transferencia' => 'Transferencia',
            // Etiquetas legacy por si se reactivan o existen datos antiguos.
            'bizum' => 'Bizum',
            'paypal' => 'PayPal',
            'efectivo' => 'Efectivo',
            default => ucfirst($code),
        };
    }

    /**
     * Convierte la marca de Stripe al valor interno de `metodo_pago`.
     */
    private function paymentMethodFromBrand(?string $marca): string
    {
        return match ($marca) {
            'visa' => 'visa',
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
            // Bizum/PayPal desactivados temporalmente.
            'metodo_pago' => 'required|in:visa,transferencia,efectivo',
            'cupon' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $tarifa = $request->tarifa;
        $metodo = $request->metodo_pago;
        $codigoCupon = $request->input('cupon');

        // Flujo de pago con tarjeta (Stripe).
        if ($metodo === 'visa') {
            if (!$user->hasDefaultPaymentMethod()) {
                return back()->with('error', 'Para pagar con tarjeta, primero añade una tarjeta.');
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

                // Envia correo para confirmar que el pago con tarjeta esta aprobado.
                $this->sendPaymentApprovedEmail(
                    $user,
                    'Tarjeta',
                    'Pago con tarjeta confirmado al cambiar plan'
                );

                return back()->with('success', 'Plan y método actualizados correctamente.');
            } catch (\Throwable $e) {
                return back()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
            }
        }

        // Los cupones solo se aceptan cuando se cobra por Stripe.
        if (!empty($codigoCupon)) {
            return back()->with('error', 'El cupón solo se aplica a pagos con tarjeta Stripe.');
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
     * Resuelve y valida un cupón para Stripe.
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
                'error' => 'El cupón no existe.',
            ];
        }

        if (!$discount->canBeUsedBy($user, $context)) {
            return [
                'model' => null,
                'stripe_coupon_id' => null,
                'error' => 'El cupón no está activo, está caducado o ya fue usado.',
            ];
        }

        if (empty($discount->stripe_coupon_id)) {
            return [
                'model' => null,
                'stripe_coupon_id' => null,
                'error' => 'El cupón no está vinculado a Stripe.',
            ];
        }

        return [
            'model' => $discount,
            'stripe_coupon_id' => $discount->stripe_coupon_id,
            'error' => null,
        ];
    }

    /**
     * Envia correo al socio cuando el pago queda aprobado.
     */
    private function sendPaymentApprovedEmail($user, string $metodo, string $origen): void
    {
        try {
            Mail::send('emails.payment-approved', [
                'nombre' => $user->nombre,
                'metodo' => $metodo,
                'tarifa' => ucfirst((string) $user->tarifa),
                'proximoCobro' => optional($user->next_payment_at)->format('d/m/Y') ?? 'Sin fecha',
                'origen' => $origen,
            ], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Pago aprobado - SeaFit');
            });
        } catch (\Throwable $e) {
            // Si falla el correo, no se rompe el flujo de pago.
            Log::error('Error al enviar correo de pago aprobado (payment controller).', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

}

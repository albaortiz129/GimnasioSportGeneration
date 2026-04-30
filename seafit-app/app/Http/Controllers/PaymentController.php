<?php

/**
 * Controlador de pagos y suscripciones del socio.
 * Gestiona tarjetas, métodos manuales, facturas y cambios de plan.
 */
namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    /**
     * IDs de productos de Stripe asociados a los tipos de suscripción.
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
        $user = Auth::user(); // Obtiene el usuario autenticado.

        // Obtiene los métodos de pago guardados en Stripe.
        $metodosPago = $user->paymentMethods();
        $metodoPrincipal = $user->defaultPaymentMethod();

        // Obtiene los métodos manuales guardados en la base de datos.
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
        $user = Auth::user(); // Obtiene el usuario autenticado.

        $suscripcion = $user->subscription('default'); // Obtiene la suscripción.

        // Verifica si existe una suscripción activa.
        if ($suscripcion && $user->subscribed('default')) {
            if ($suscripcion->onGracePeriod()) { // Evita duplicar la solicitud si la suscripción ya está marcada para expirar.
                return back()->with('success', 'Tu suscripción ya estaba programada para cancelarse al final del período.');
            }

            $suscripcion->cancel(); // Se cancela en Stripe pero mantiene acceso hasta fin de ciclo.

            return back()->with('success', 'Tu suscripción ha sido cancelada. Tendrás acceso que se termine la suscripción actual.');
        }

        // Pagos manuales en efectivo.
        if ($user->tarifa === 'cancelada') { // Evita duplicar la solicitud si la suscripción ya está marcada para expirar.
            return back()->with('success', 'Tu cancelación ya estaba programada para el final del período actual.');
        }

        if (!$user->isPlanActive()) { // Si el plan no está activo, no hay suscripción para cancelar.
            return back()->with('error', 'No tienes una suscripción activa para cancelar.');
        }

        $fechaFin = $user->next_payment_at // Calcula la fecha de fin.
            ? Carbon::parse($user->next_payment_at)->toDateString()
            : $this->nextChargeFromPlan((string) $user->tarifa);

        $user->update([ // Actualiza el estado de la suscripción.
            'tarifa' => 'cancelada',
            'payment_status' => 'al_dia',
            'next_payment_at' => $fechaFin,
        ]);

        $fechaVisible = $fechaFin ? Carbon::parse($fechaFin)->format('d/m/Y') : 'fin del período actual'; // Formatea la fecha de fin.

        return back()->with('success', "Tu suscripción se cancelará al final del período actual ({$fechaVisible})."); // Devuelve un mensaje de éxito.
    }

    /**
     * Reanuda suscripción o crea una nueva.
     * También permite cambiar de plan.
     */
    public function resumePlan(Request $request)
    {
        $request->validate([
            'tarifa' => 'nullable|in:mensual,trimestral,anual',
            'cupon' => 'nullable|string|max:100',
        ]);

        $user = Auth::user(); // Obtiene el usuario autenticado.
        $nuevaTarifa = $request->input('tarifa', 'mensual'); // Obtiene la tarifa del plan.
        $codigoCupon = $request->input('cupon'); // Obtiene el código del cupón de descuento.

        // Valida el cupón de descuento solo si el usuario ha escrito uno.
        $cupon = $this->resolveStripeCoupon($codigoCupon, $user, 'reanudar_plan'); // Obtiene el código descuento.
        if ($cupon['error']) { // Si el cupón no es válido,
            return back()->with('error', $cupon['error']); // Devuelve el error.
        }

        $planId = $this->priceIdFromPlan($nuevaTarifa); // Obtiene el ID del plan.

        try {
            $subscription = $user->subscription('default'); // Obtiene la suscripción.

            if ($subscription) { // Si la suscripción existe, actualiza la tarifa del plan.
                if ($user->tarifa !== $nuevaTarifa) { // Si la tarifa es diferente, cambia la suscripción.
                    $subscription->swap($planId);
                }

                if ($cupon['stripe_coupon_id']) { // Si el cupón es válido, aplica el cupón.
                    $subscription->applyCoupon($cupon['stripe_coupon_id']);
                }

                if ($subscription->onGracePeriod()) { // Si la suscripción está en período de gracia, reanuda la suscripción.
                    $subscription->resume(); // Reanuda la suscripción.
                }
            } else { // Si no existe, se crea desde cero con el método por defecto.
                if (!$user->hasDefaultPaymentMethod()) { // Si el usuario no tiene un método de pago por defecto, devuelve un error.
                    return back()->with('error', 'No tienes una tarjeta guardada.');
                }

                $newSubscription = $user->newSubscription('default', $planId); // Crea una nueva suscripción.

                if ($cupon['stripe_coupon_id']) { // Si el cupón es válido, se aplica.
                    $newSubscription->withCoupon($cupon['stripe_coupon_id']);
                }

                $newSubscription->create($user->defaultPaymentMethod()->id); // Crea la suscripción.
            }

            $user->update([
                'tarifa' => $nuevaTarifa,
                'payment_status' => 'al_dia',
                'next_payment_at' => $this->nextChargeFromPlan($nuevaTarifa),
            ]);

            if ($cupon['model']) { // Si el cupón es válido, se marca como usado.
                $descuentoAplicado = $cupon['model']->calculateDiscountAmount($this->planBaseAmount($nuevaTarifa)); // Calcula el descuento aplicado.
                $cupon['model']->markUsed($user, 'reanudar_plan', $descuentoAplicado); // Marca el cupón como usado.
            }

            // Envía correo de confirmación.
            $this->sendPaymentApprovedEmail(
                $user,
                'Tarjeta',
                'Pago con tarjeta confirmado.'
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
            $user->updateDefaultPaymentMethod($paymentMethodId); // Actualiza el método de pago por defecto.
            $metodoStripe = $user->findPaymentMethod($paymentMethodId); // Busca el método de pago.
            $marca = strtolower((string) optional($metodoStripe->card ?? null)->brand); // Obtiene la marca de la tarjeta.

            $user->update([
                'metodo_pago' => $this->paymentMethodFromBrand($marca), // Actualiza el método de pago.
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
        $paymentMethodId = $request->input('payment_method'); // Obtiene el ID del método de pago.

        $paymentMethod = $user->findPaymentMethod($paymentMethodId); // Busca el método de pago.

        if (!$paymentMethod) { // Si el método de pago no se encuentra, devuelve un error.
            return back()->with('error', 'No se pudo encontrar el método de pago.');
        }

        $paymentMethod->delete(); // Elimina el método de pago.

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
     * Pantalla para añadir una tarjeta.
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

        if (!$user->stripe_id) { // Si el usuario no existe en Stripe, lo registra como cliente para poder asignarle tarjeta.
            $user->createAsStripeCustomer();
        }

        try {
            $user->addPaymentMethod($request->payment_method); // Añade la nueva tarjeta al usuario.

            if (!$user->hasDefaultPaymentMethod()) { // Si el usuario no tiene un método de pago por defecto, se establece el nuevo como predeterminado.
                $user->updateDefaultPaymentMethod($request->payment_method);
            }

            return redirect()->route('pago.gestion')->with('success', 'Tarjeta añadida correctamente.');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo guardar la tarjeta: ' . $e->getMessage());
        }
    }

    /**
     * Guarda o actualiza el método manual activo.
     */
    public function saveManualMethod(Request $request)
    {
        $data = $request->validate([
            'metodo_manual' => 'required|in:efectivo', // Solo se permite efectivo como método manual.
        ], [
            'metodo_manual.required' => 'Selecciona un método manual.',
            'metodo_manual.in' => 'Método manual no válido.',
        ]);

        $user = Auth::user();
        $manuales = collect($user->manual_payment_methods ?? []) // Recolecta los métodos de pago manuales del usuario.
            ->map(fn($metodo) => $this->normalizeManualMethod($metodo)) // Normaliza los métodos de pago manuales.
            ->filter() // Filtra los métodos de pago manuales.
            ->keyBy('code'); // Asigna el código de método manual.

        $yaExistia = $manuales->has($data['metodo_manual']); // Comprueba si el método manual ya existía.

        $manuales->put($data['metodo_manual'], [
            'code' => $data['metodo_manual'], // Guarda el código de método manual.
            'value' => null,
        ]);

        $user->manual_payment_methods = $manuales->values()->all(); // Actualiza los métodos de pago manuales del usuario.

        // Si el usuario tenía tarjeta como principal, al guardar manual pasamos a efectivo.
        if ($user->metodo_pago !== 'efectivo') {
            $user->metodo_pago = $data['metodo_manual']; // Actualiza el método de pago del usuario.
        }

        $user->save(); // Guarda los cambios en base de datos.

        return back()->with('success', $yaExistia ? 'Método manual actualizado.' : 'Método manual guardado.');
    }

    /**
     * Pone un método manual como método principal.
     */
    public function setPrimaryManualMethod(Request $request)
    {
        $data = $request->validate([
            'metodo_manual' => 'required|in:efectivo', // Solo se permite efectivo como método manual.
        ]);

        $user = Auth::user();
        $manuales = collect($user->manual_payment_methods ?? []) // Recolecta los métodos de pago manuales del usuario.
            ->map(fn($metodo) => $this->normalizeManualMethod($metodo)) // Normaliza los métodos de pago manuales.
            ->filter() // Filtra los métodos de pago manuales.
            ->values(); // Obtiene los valores de los métodos de pago manuales.

        if (!$manuales->contains(fn($metodo) => $metodo['code'] === $data['metodo_manual'])) { // Comprueba si el método manual existe.
            return back()->with('error', 'Método no encontrado.'); // Si no existe, devuelve un error.
        }

        $user->update([
            'metodo_pago' => $data['metodo_manual'], // Actualiza el método de pago del usuario.
        ]);

        return back()->with('success', 'Método principal actualizado.');
    }

    /**
     * Elimina un método manual guardado.
     */
    public function deleteManualMethod(Request $request)
    {
        $data = $request->validate([
            'metodo_manual' => 'required|in:efectivo',
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

        // Si se borra el método principal, se intenta seleccionar otro automáticamente.
        if ($user->metodo_pago === $data['metodo_manual']) {
            if ($manuales->isNotEmpty()) { // Si hay métodos manuales, se selecciona el primero.
                $user->metodo_pago = $manuales->first()['code']; // Actualiza el método de pago del usuario.
            } elseif ($user->hasDefaultPaymentMethod()) { // Si hay un método de pago por defecto, se selecciona.
                $brand = strtolower((string) optional($user->defaultPaymentMethod()->card)->brand); // Obtiene la marca de la tarjeta.
                $user->metodo_pago = $this->paymentMethodFromBrand($brand);
            }
        }

        $user->save();

        return back()->with('success', 'Método manual eliminado.');
    }

    /**
     * Normaliza el método manual
     */
    private function normalizeManualMethod(mixed $metodo): ?array
    {
        if (!is_array($metodo)) {
            return null;
        }

        $code = strtolower(trim((string) ($metodo['code'] ?? '')));

        if ($code !== 'efectivo') {
            return null;
        }

        $value = trim((string) ($metodo['value'] ?? ''));
        $value = $value === '' ? null : $value;

        return [
            'code' => $code,
            'label' => $this->manualMethodName($code),
            'value' => $value,
            'value_masked' => $this->maskManualData($value),
        ];
    }

    /**
     * Devuelve el dato manual listo para mostrar.
     * En efectivo no se guarda información sensible, así que no se enmascara.
     */
    private function maskManualData(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return $value;
    }

    /**
     * Devuelve el nombre visible de un método manual.
     */
    private function manualMethodName(string $code): string
    {
        return match ($code) {
            'efectivo' => 'Efectivo',
            default => ucfirst($code),
        };
    }

    /**
     * Convierte la marca de Stripe al valor interno de `metodo_pago`.
     */
    private function paymentMethodFromBrand(?string $marca): string
    {
        // Para mantener el sistema sencillo, cualquier marca de tarjeta se guarda como "visa".
        return 'visa';
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
            // Métodos permitidos: tarjeta y efectivo.
            'metodo_pago' => 'required|in:visa,efectivo',
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

                // Envía correo para confirmar que el pago con tarjeta está aprobado.
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
     * Envía correo al socio cuando el pago queda aprobado.
     */
    private function sendPaymentApprovedEmail(User $user, string $metodo, string $origen): void
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

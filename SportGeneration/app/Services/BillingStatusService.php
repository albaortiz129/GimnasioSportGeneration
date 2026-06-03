<?php

namespace App\Services;

use App\Mail\PaymentUnpaidAdminMail;
use App\Mail\PaymentUnpaidUserMail;
use App\Models\AppSetting;
use App\Models\User;
use App\Support\MailAddresses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class BillingStatusService
{
    private const UNPAID_NOTIFICATION_EMAIL_KEY = 'unpaid_notification_email';

    public function markOverduePayments(?Carbon $today = null): array
    {
        if (!Schema::hasColumn('users', 'payment_status') || !Schema::hasColumn('users', 'next_payment_at')) {
            return ['updated' => 0, 'emails_failed' => 0];
        }

        $today = ($today ?? today())->copy()->startOfDay();
        $updated = 0;
        $emailsFailed = 0;

        User::query()
            ->where('is_admin', false)
            ->where('payment_status', '!=', 'impagado')
            ->whereNotNull('next_payment_at')
            ->whereDate('next_payment_at', '<', $today->toDateString())
            ->orderBy('id')
            ->chunkById(50, function ($users) use (&$updated, &$emailsFailed): void {
                foreach ($users as $user) {
                    $user->update(['payment_status' => 'impagado']);
                    $updated++;

                    $envios = $this->sendPaymentUnpaidEmail(
                        $user->refresh(),
                        'Cuenta marcada como impagada automáticamente por fecha de cobro vencida',
                        'markOverduePayments'
                    );

                    if (!$envios['interno'] || !$envios['cliente']) {
                        $emailsFailed++;
                    }
                }
            });

        return ['updated' => $updated, 'emails_failed' => $emailsFailed];
    }

    public function sendPaymentUnpaidEmail(User $user, string $origen, string $context): array
    {
        $metodo = $this->paymentMethodLabel($user->metodo_pago);
        $proximoCobro = $this->formatNextPaymentDate($user);
        $enviadoInterno = false;
        $enviadoCliente = false;

        try {
            $internalEmail = $this->unpaidNotificationEmail();

            if ($internalEmail) {
                Mail::to($internalEmail)->send(new PaymentUnpaidAdminMail(
                    $user,
                    $metodo,
                    $proximoCobro,
                    $origen
                ));
                $enviadoInterno = true;
            }
        } catch (\Throwable $e) {
            Log::error("Error al enviar correo interno de impago ({$context}).", [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            Mail::to($user->email)->send(new PaymentUnpaidUserMail(
                $user,
                $metodo,
                $proximoCobro,
                $origen
            ));
            $enviadoCliente = true;
        } catch (\Throwable $e) {
            Log::error("Error al enviar correo de impago al cliente ({$context}).", [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'interno' => $enviadoInterno,
            'cliente' => $enviadoCliente,
        ];
    }

    private function paymentMethodLabel(?string $method): string
    {
        return match (strtolower((string) $method)) {
            'visa' => 'Tarjeta',
            'efectivo' => 'Efectivo',
            default => 'Método manual',
        };
    }

    private function unpaidNotificationEmail(): ?string
    {
        if (!Schema::hasTable('app_settings')) {
            return null;
        }

        $email = trim((string) AppSetting::getValue(
            self::UNPAID_NOTIFICATION_EMAIL_KEY,
            MailAddresses::defaultAdminNotificationEmail()
        ));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function formatNextPaymentDate(User $user): string
    {
        if (empty($user->next_payment_at)) {
            return 'Sin fecha';
        }

        try {
            return Carbon::parse((string) $user->next_payment_at)->format('d/m/Y');
        } catch (\Throwable) {
            return 'Sin fecha';
        }
    }
}

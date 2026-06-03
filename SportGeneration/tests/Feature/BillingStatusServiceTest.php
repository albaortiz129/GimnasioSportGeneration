<?php

namespace Tests\Feature;

use App\Mail\PaymentUnpaidAdminMail;
use App\Mail\PaymentUnpaidUserMail;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\BillingStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BillingStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_active_clients_are_marked_as_unpaid_and_notified(): void
    {
        Mail::fake();

        AppSetting::setValue('unpaid_notification_email', 'impagos@example.com');

        $client = User::factory()->create([
            'email' => 'client@example.com',
            'is_admin' => false,
            'payment_status' => 'al_dia',
            'next_payment_at' => today()->subDay(),
            'metodo_pago' => 'visa',
            'tarifa' => 'mensual',
        ]);

        $result = app(BillingStatusService::class)->markOverduePayments();

        $this->assertSame(1, $result['updated']);
        $this->assertSame('impagado', $client->refresh()->payment_status);

        Mail::assertSent(PaymentUnpaidAdminMail::class, fn(PaymentUnpaidAdminMail $mail) => $mail->hasTo('impagos@example.com')
            && $mail->user->is($client)
            && str_contains($mail->origen, 'automáticamente'));

        Mail::assertSent(PaymentUnpaidUserMail::class, fn(PaymentUnpaidUserMail $mail) => $mail->hasTo('client@example.com')
            && $mail->user->is($client)
            && str_contains($mail->origen, 'automáticamente'));
    }

    public function test_clients_due_today_are_not_marked_as_unpaid(): void
    {
        Mail::fake();

        AppSetting::setValue('unpaid_notification_email', 'impagos@example.com');

        $client = User::factory()->create([
            'is_admin' => false,
            'payment_status' => 'al_dia',
            'next_payment_at' => today(),
        ]);

        $result = app(BillingStatusService::class)->markOverduePayments();

        $this->assertSame(0, $result['updated']);
        $this->assertSame('al_dia', $client->refresh()->payment_status);
        Mail::assertNothingSent();
    }

    public function test_client_paid_until_day_ten_becomes_unpaid_on_day_eleven(): void
    {
        Mail::fake();

        AppSetting::setValue('unpaid_notification_email', 'impagos@example.com');

        $client = User::factory()->create([
            'email' => 'client@example.com',
            'is_admin' => false,
            'payment_status' => 'pendiente',
            'next_payment_at' => '2026-06-10',
        ]);

        $result = app(BillingStatusService::class)->markOverduePayments(
            \Carbon\Carbon::parse('2026-06-11')
        );

        $this->assertSame(1, $result['updated']);
        $this->assertSame('impagado', $client->refresh()->payment_status);
    }
}

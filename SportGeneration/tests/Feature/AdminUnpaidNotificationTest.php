<?php

namespace Tests\Feature;

use App\Mail\PaymentUnpaidAdminMail;
use App\Mail\PaymentUnpaidUserMail;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminUnpaidNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_a_client_as_unpaid_sends_internal_notification_and_user_email(): void
    {
        Mail::fake();

        AppSetting::setValue('unpaid_notification_email', 'impagos@example.com');

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $client = User::factory()->create([
            'email' => 'client@example.com',
            'is_admin' => false,
            'payment_status' => 'al_dia',
            'metodo_pago' => 'efectivo',
            'tarifa' => 'mensual',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.user.mark_unpaid', $client))
            ->assertRedirect();

        $this->assertSame('impagado', $client->refresh()->payment_status);

        Mail::assertSent(PaymentUnpaidAdminMail::class, function (PaymentUnpaidAdminMail $mail) use ($client) {
            return $mail->hasTo('impagos@example.com')
                && $mail->user->is($client)
                && $mail->origen === 'Cuenta marcada como impagada por administración';
        });

        Mail::assertSent(PaymentUnpaidUserMail::class, function (PaymentUnpaidUserMail $mail) use ($client) {
            return $mail->hasTo('client@example.com')
                && $mail->user->is($client)
                && $mail->origen === 'Cuenta marcada como impagada por administración';
        });
    }

    public function test_marking_an_already_unpaid_client_resends_internal_notification_and_user_email(): void
    {
        Mail::fake();

        AppSetting::setValue('unpaid_notification_email', 'impagos@example.com');

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $client = User::factory()->create([
            'email' => 'client@example.com',
            'is_admin' => false,
            'payment_status' => 'impagado',
            'metodo_pago' => 'efectivo',
            'tarifa' => 'mensual',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.user.mark_unpaid', $client))
            ->assertRedirect();

        Mail::assertSent(PaymentUnpaidAdminMail::class, function (PaymentUnpaidAdminMail $mail) use ($client) {
            return $mail->hasTo('impagos@example.com')
                && $mail->user->is($client)
                && str_contains($mail->origen, 'Aviso reenviado');
        });

        Mail::assertSent(PaymentUnpaidUserMail::class, function (PaymentUnpaidUserMail $mail) use ($client) {
            return $mail->hasTo('client@example.com')
                && $mail->user->is($client)
                && str_contains($mail->origen, 'Aviso reenviado');
        });
    }
}

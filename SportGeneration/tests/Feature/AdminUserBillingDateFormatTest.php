<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserBillingDateFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_updates_next_payment_date_using_spanish_format(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $client = User::factory()->create([
            'is_admin' => false,
            'payment_status' => 'al_dia',
            'next_payment_at' => null,
            'tarifa' => 'mensual',
            'metodo_pago' => 'visa',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.user.update', $client), [
                'nombre' => $client->nombre,
                'apellidos' => $client->apellidos,
                'dni' => $client->dni,
                'fecha_nacimiento' => optional($client->fecha_nacimiento)->format('Y-m-d') ?? '2000-01-01',
                'telefono' => $client->telefono,
                'email' => $client->email,
                'domicilio' => $client->domicilio,
                'tarifa' => 'mensual',
                'metodo_pago' => 'visa',
                'payment_status' => 'al_dia',
                'next_payment_at' => '11/06/2026',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertSame('2026-06-11', $client->refresh()->next_payment_at->toDateString());
    }
}

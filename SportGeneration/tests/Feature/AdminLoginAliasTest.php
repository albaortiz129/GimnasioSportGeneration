<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginAliasTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_admin_alias(): void
    {
        $admin = User::factory()->create([
            'email' => 'real-admin@example.com',
            'password' => Hash::make('clave-admin'),
            'is_admin' => true,
        ]);

        $this->post('/login', [
            'email' => 'admin',
            'password' => 'clave-admin',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_login_with_unpaid_notification_email_alias(): void
    {
        $admin = User::factory()->create([
            'email' => 'real-admin@example.com',
            'password' => Hash::make('clave-admin'),
            'is_admin' => true,
        ]);

        AppSetting::setValue('unpaid_notification_email', 'impagos-admin@example.com');

        $this->post('/login', [
            'email' => 'impagos-admin@example.com',
            'password' => 'clave-admin',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }
}

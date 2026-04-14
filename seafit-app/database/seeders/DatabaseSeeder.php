<?php

/**
 * Seeder principal: inicializa datos base y crea el usuario administrador.
 */
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Clases base y garantiza un usuario administrador.
     */
    public function run(): void
    {
        // 1) Poblar clases semanales de SeaFit.
        $this->call([
            GymClassSeeder::class,
        ]);

        // 2) Crear/actualizar admin por variables de entorno.
        $adminEmail = env('ADMIN_EMAIL', 'soporte.seafit@gmail.com');
        $adminPass = env('ADMIN_PASSWORD', 'seafit12');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'nombre' => 'Administrador',
                'apellidos' => 'SeaFit',
                'dni' => '00000000X',
                'fecha_nacimiento' => '2000-01-01',
                'telefono' => '000000000',
                'email' => $adminEmail,
                'domicilio' => 'Soporte SeaFit',
                'tarifa' => 'Admin',
                'metodo_pago' => 'Ninguno',
                'password' => Hash::make($adminPass),
                'is_admin' => true,
            ]
        );
    }
}


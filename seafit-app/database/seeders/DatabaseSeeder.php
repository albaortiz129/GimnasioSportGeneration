<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ClaseSeeder::class,
        ]);

        $adminEmail = env('ADMIN_EMAIL', 'soporte.seafit@gmail.com');
        $adminPass = env('ADMIN_PASSWORD', 'seafit12');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'nombre' => 'Administrador',
                'apellidos' => 'SeaFit',
                'dni' => '00000000X',           // Valor por defecto
                'fecha_nacimiento' => '2000-01-01', // Valor por defecto
                'telefono' => '000000000',       // Valor por defecto
                'email' => $adminEmail,
                'domicilio' => 'Soporte SeaFit', // Valor por defecto
                'tarifa' => 'Admin',             // O la que tengas por defecto
                'metodo_pago' => 'Ninguno',
                'password' => Hash::make($adminPass),
                'is_admin' => true,
            ]
        );
    }
}
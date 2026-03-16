<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llama al seeder de las clases para que la agenda no salga vacía
        $this->call([
            ClaseSeeder::class,
        ]);

        // Crea un usuario de prueba para que puedas entrar sin registrarte
        User::factory()->create([
            'nombre' => 'Test',
            'apellidos' => 'User',
            'email' => 'test@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
        ]);
    }
}

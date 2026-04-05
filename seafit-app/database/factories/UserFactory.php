<?php

/**
 * Factory de usuarios para pruebas automatizadas y generacion de datos de ejemplo.
 */
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Cache del hash de password para no recalcular en cada usuario fake.
     */
    protected static ?string $password;

    /**
     * Datos por defecto para crear usuarios de prueba.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'dni' => fake()->unique()->bothify('########?'),
            'fecha_nacimiento' => '1990-01-01',
            'telefono' => '600112233',
            'email' => fake()->unique()->safeEmail(),
            'domicilio' => 'Calle Falsa 123',
            'tarifa' => 'mensual',
            'metodo_pago' => 'bizum',
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Variante con email sin verificar.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

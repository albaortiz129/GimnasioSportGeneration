<?php

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
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'nombre' => fake()->firstName(),
        'apellidos' => fake()->lastName(),
        'dni' => fake()->unique()->bothify('########?'), // Genera algo como 12345678A
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
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

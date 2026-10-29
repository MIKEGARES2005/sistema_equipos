<?php

namespace Database\Factories;

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'tipo_usuario' => 'administrador',
            'docente_id' => null,
            'estudiante_id' => null,
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
            'activo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Cuenta de administrador sin perfil personal asociado.
     */
    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_usuario' => 'administrador',
            'docente_id' => null,
            'estudiante_id' => null,
        ]);
    }

    /**
     * Cuenta de docente vinculada a su perfil personal.
     */
    public function docente(Docente $docente): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_usuario' => 'docente',
            'docente_id' => $docente->id,
            'estudiante_id' => null,
        ]);
    }

    /**
     * Cuenta de estudiante vinculada a su perfil personal.
     */
    public function estudiante(Estudiante $estudiante): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_usuario' => 'estudiante',
            'docente_id' => null,
            'estudiante_id' => $estudiante->id,
        ]);
    }

    /**
     * Cuenta desactivada: el acceso se corta por `users.activo`.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Cuenta bloqueada temporalmente tras superar los intentos permitidos.
     */
    public function bloqueado(int $minutos = 15): static
    {
        return $this->state(fn (array $attributes) => [
            'intentos_fallidos' => 5,
            'bloqueado_hasta' => now()->addMinutes($minutos),
        ]);
    }
}

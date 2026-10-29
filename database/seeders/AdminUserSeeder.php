<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Cuenta administrativa base. Es idempotente: se puede volver a ejecutar
     * el seeder sin duplicar la cuenta.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => env('SEED_ADMIN_USERNAME', 'admin')],
            [
                'password' => env('SEED_ADMIN_PASSWORD', 'password'),
                'tipo_usuario' => 'administrador',
                'docente_id' => null,
                'estudiante_id' => null,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
                'activo' => true,
            ],
        );
    }
}

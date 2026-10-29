<?php

namespace Database\Seeders;

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Crea cuentas de demostración para probar ambos portales.
     * No se ejecuta automáticamente desde DatabaseSeeder.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        $docente = Docente::updateOrCreate(
            ['ci' => '90000001'],
            [
                'nombres' => 'Docente',
                'apellido_paterno' => 'Demostración',
                'apellido_materno' => 'Prueba',
                'ci_extension' => 'LP',
                'telefono' => '70000001',
                'correo_personal' => 'docente.demo@example.com',
                'cargo' => 'Docente de laboratorio',
                'especialidad' => 'Redes y sistemas',
                'nivel_academico' => 'Licenciatura',
                'estado' => 'activo',
            ],
        );

        User::updateOrCreate(
            ['username' => 'docente.demo'],
            [
                'password' => 'Password123!',
                'tipo_usuario' => 'docente',
                'docente_id' => $docente->id,
                'estudiante_id' => null,
                'activo' => true,
                'debe_cambiar_password' => false,
            ],
        );

        $estudianteActivo = Estudiante::updateOrCreate(
            ['matricula' => 'DEMO-0001'],
            [
                'nombres' => 'Estudiante',
                'apellido_paterno' => 'Demostración',
                'apellido_materno' => 'Activo',
                'semestre' => 3,
                'generacion_ingreso' => 'I-2024',
                'ci' => '90000002',
                'ci_extension' => 'LP',
                'telefono' => '70000002',
                'correo_institucional' => 'estudiante.demo@itibb.edu.bo',
                'correo_personal' => 'estudiante.demo@example.com',
                'direccion' => 'Av. Demostración #123',
                'estado' => 'activo',
            ],
        );

        User::updateOrCreate(
            ['username' => 'estudiante.demo'],
            [
                'password' => 'Password123!',
                'tipo_usuario' => 'estudiante',
                'docente_id' => null,
                'estudiante_id' => $estudianteActivo->id,
                'activo' => true,
                'debe_cambiar_password' => false,
            ],
        );

        $estudianteCongelado = Estudiante::updateOrCreate(
            ['matricula' => 'DEMO-0002'],
            [
                'nombres' => 'Estudiante',
                'apellido_paterno' => 'Demostración',
                'apellido_materno' => 'Congelado',
                'semestre' => 2,
                'generacion_ingreso' => 'I-2025',
                'ci' => '90000003',
                'ci_extension' => 'LP',
                'estado' => 'congelado',
            ],
        );

        User::updateOrCreate(
            ['username' => 'estudiante.congelado'],
            [
                'password' => 'Password123!',
                'tipo_usuario' => 'estudiante',
                'docente_id' => null,
                'estudiante_id' => $estudianteCongelado->id,
                'activo' => true,
                'debe_cambiar_password' => false,
            ],
        );

        $estudianteEgresado = Estudiante::updateOrCreate(
            ['matricula' => 'DEMO-0003'],
            [
                'nombres' => 'Estudiante',
                'apellido_paterno' => 'Demostración',
                'apellido_materno' => 'Egresado',
                'semestre' => 4,
                'generacion_ingreso' => 'I-2023',
                'ci' => '90000004',
                'ci_extension' => 'LP',
                'estado' => 'egresado',
            ],
        );

        User::updateOrCreate(
            ['username' => 'estudiante.egresado'],
            [
                'password' => 'Password123!',
                'tipo_usuario' => 'estudiante',
                'docente_id' => null,
                'estudiante_id' => $estudianteEgresado->id,
                'activo' => true,
                'debe_cambiar_password' => false,
            ],
        );

        $estudianteInactivo = Estudiante::updateOrCreate(
            ['matricula' => 'DEMO-0004'],
            [
                'nombres' => 'Estudiante',
                'apellido_paterno' => 'Demostración',
                'apellido_materno' => 'Inactivo',
                'semestre' => 1,
                'generacion_ingreso' => 'I-2025',
                'ci' => '90000005',
                'ci_extension' => 'LP',
                'estado' => 'inactivo',
            ],
        );

        User::updateOrCreate(
            ['username' => 'estudiante.inactivo'],
            [
                'password' => 'Password123!',
                'tipo_usuario' => 'estudiante',
                'docente_id' => null,
                'estudiante_id' => $estudianteInactivo->id,
                'activo' => true,
                'debe_cambiar_password' => false,
            ],
        );

        $estudianteRetirado = Estudiante::updateOrCreate(
            ['matricula' => 'DEMO-0005'],
            [
                'nombres' => 'Estudiante',
                'apellido_paterno' => 'Demostración',
                'apellido_materno' => 'Retirado',
                'semestre' => 4,
                'generacion_ingreso' => 'I-2023',
                'ci' => '90000006',
                'ci_extension' => 'LP',
                'estado' => 'retirado',
            ],
        );

        User::updateOrCreate(
            ['username' => 'estudiante.retirado'],
            [
                'password' => 'Password123!',
                'tipo_usuario' => 'estudiante',
                'docente_id' => null,
                'estudiante_id' => $estudianteRetirado->id,
                'activo' => true,
                'debe_cambiar_password' => false,
            ],
        );

        $estudianteDesactivado = Estudiante::updateOrCreate(
            ['matricula' => 'DEMO-0006'],
            [
                'nombres' => 'Estudiante',
                'apellido_paterno' => 'Demostración',
                'apellido_materno' => 'Desactivado',
                'semestre' => 2,
                'generacion_ingreso' => 'I-2024',
                'ci' => '90000007',
                'ci_extension' => 'LP',
                'estado' => 'activo',
            ],
        );

        User::updateOrCreate(
            ['username' => 'estudiante.desactivado'],
            [
                'password' => 'Password123!',
                'tipo_usuario' => 'estudiante',
                'docente_id' => null,
                'estudiante_id' => $estudianteDesactivado->id,
                'activo' => false,
                'debe_cambiar_password' => false,
            ],
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUsersSeeder extends Seeder
{
    /**
     * Crea las cuentas de prueba para validar ambos portales y la recuperación
     * de contraseña por preguntas de seguridad.
     *
     * No se ejecuta automáticamente desde DatabaseSeeder.
     */
    public function run(): void
    {
        $docenteMagaly = Docente::updateOrCreate(
            ['ci' => '17458521'],
            [
                'nombres' => 'Magaly',
                'apellido_paterno' => 'Vega',
                'apellido_materno' => 'Flores',
                'ci_extension' => 'LP',
                'telefono' => '78451265',
                'correo_personal' => 'magalyguara23@gmail.com',
                'cargo' => 'docente',
                'especialidad' => 'Sistemas Informaticos',
                'nivel_academico' => 'Ingeniera',
                'fecha_ingreso' => '2005-02-12',
                'estado' => 'activo',
            ],
        );

        $administrador = User::updateOrCreate(
            ['username' => 'magaly'],
            [
                'password' => 'magaly123',
                'tipo_usuario' => 'administrador',
                'docente_id' => $docenteMagaly->id,
                'estudiante_id' => null,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
                'activo' => true,
                'debe_cambiar_password' => false,
                'username_cambiado_en' => null,
                'bloqueos_consecutivos' => 0,
                'ultimo_acceso' => null,
            ],
        );

        $this->configurarPreguntas($administrador, [
            ['pregunta' => '¿Cual es tu color favorito?', 'respuesta' => 'Azul'],
            ['pregunta' => '¿Cual es el nombre de mi mascota?', 'respuesta' => 'Bobby'],
            ['pregunta' => '¿Cual es mi apodo de la infancia?', 'respuesta' => 'Maggy'],
        ]);

        $docenteBetty = Docente::updateOrCreate(
            ['ci' => '17894523'],
            [
                'nombres' => 'Betty',
                'apellido_paterno' => 'Chura',
                'apellido_materno' => 'Choquehuanca',
                'ci_extension' => 'LP',
                'telefono' => '77784512',
                'correo_personal' => 'bettychoque3@gmail.com',
                'cargo' => 'Docente',
                'especialidad' => 'Base de Datos',
                'nivel_academico' => 'Tecnica Superior',
                'fecha_ingreso' => '2008-03-25',
                'estado' => 'activo',
            ],
        );

        $docente = User::updateOrCreate(
            ['username' => 'betty'],
            [
                'password' => 'betty123',
                'tipo_usuario' => 'docente',
                'docente_id' => $docenteBetty->id,
                'estudiante_id' => null,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
                'activo' => true,
                'debe_cambiar_password' => false,
                'username_cambiado_en' => null,
                'bloqueos_consecutivos' => 0,
                'ultimo_acceso' => null,
            ],
        );

        $this->configurarPreguntas($docente, [
            ['pregunta' => '¿Año de nacimiento?', 'respuesta' => '1989'],
            ['pregunta' => '¿Cual es el nombre de mi Madre?', 'respuesta' => 'Sofia'],
            ['pregunta' => '¿Cual es el apodo de mi yerna?', 'respuesta' => 'Emmita'],
        ]);

        $estudianteMiguel = Estudiante::updateOrCreate(
            ['matricula' => 'VF8354M'],
            [
                'nombres' => 'Miguel Angel',
                'apellido_paterno' => 'Vega',
                'apellido_materno' => 'Flores',
                'semestre' => 3,
                'generacion_ingreso' => 'II-2025',
                'ci' => '15645072',
                'ci_extension' => 'LP',
                'telefono' => '63084643',
                'correo_institucional' => 'miguelvegaflores@itibb.edu.bo',
                'correo_personal' => 'mikegares2405@gmail.com',
                'direccion' => 'Av. Mariscal Andres de Santa Cruz, Zn 21 de Septiembre',
                'estado' => 'activo',
            ],
        );

        $estudiante = User::updateOrCreate(
            ['username' => 'miguel_vega'],
            [
                'password' => 'miguel123',
                'tipo_usuario' => 'estudiante',
                'docente_id' => null,
                'estudiante_id' => $estudianteMiguel->id,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
                'activo' => true,
                'debe_cambiar_password' => false,
                'username_cambiado_en' => null,
                'bloqueos_consecutivos' => 0,
                'ultimo_acceso' => null,
            ],
        );

        $this->configurarPreguntas($estudiante, [
            ['pregunta' => '¿Cual es mi alias?', 'respuesta' => 'mikegares'],
            ['pregunta' => '¿Cual es mi numero favorito?', 'respuesta' => '21'],
            ['pregunta' => '¿Cual es el nombre de mi novia?', 'respuesta' => 'Ivon'],
        ]);
    }

    /**
     * Reemplaza las preguntas de seguridad del usuario para que el seeder sea idempotente.
     *
     * @param  array<int, array{pregunta: string, respuesta: string}>  $preguntas
     */
    private function configurarPreguntas(User $user, array $preguntas): void
    {
        $user->preguntasSeguridad()->delete();

        foreach ($preguntas as $pregunta) {
            $user->preguntasSeguridad()->create([
                'pregunta' => $pregunta['pregunta'],
                'respuesta_hash' => Hash::make($this->normalizarRespuesta($pregunta['respuesta'])),
            ]);
        }
    }

    private function normalizarRespuesta(string $respuesta): string
    {
        return Str::ascii(Str::lower(trim($respuesta)));
    }
}

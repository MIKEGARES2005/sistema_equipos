<?php

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\User;

beforeEach(function (): void {
    $this->withoutVite();
});

test('teachers register the first partial and administrators are notified', function () {
    $admin = gradeAdminActor();
    $teacher = gradeTeacherProfile();
    $teacherUser = gradeTeacherActor($teacher);
    $enrollment = gradeEnrollment($teacher, ['parcial_1' => null, 'parcial_2' => null]);

    $this->actingAs($teacherUser)
        ->patch(route('inscripciones.register-partial', $enrollment), [
            'parcial' => '1',
            'nota' => 80,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('inscripciones', [
        'id' => $enrollment->id,
        'parcial_1' => 80,
        'parcial_1_por' => $teacherUser->id,
        'parcial_2' => null,
        'nota_final' => null,
    ]);

    $this->assertDatabaseHas('notificaciones', [
        'user_id' => $admin->id,
        'tipo' => 'calificacion',
    ]);

    $this->assertDatabaseHas('bitacora', [
        'accion' => 'registrar_parcial_1',
        'entidad' => 'inscripciones',
        'entidad_id' => $enrollment->id,
    ]);
});

test('the second partial calculates the final grade as the simple average', function () {
    $admin = gradeAdminActor();
    $teacher = gradeTeacherProfile();
    $teacherUser = gradeTeacherActor($teacher);
    $enrollment = gradeEnrollment($teacher, ['parcial_1' => 80, 'parcial_2' => null]);

    $this->actingAs($teacherUser)
        ->patch(route('inscripciones.register-partial', $enrollment), [
            'parcial' => '2',
            'nota' => 91,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $enrollment->refresh();

    expect((float) $enrollment->nota_final)->toBe(85.5)
        ->and($enrollment->nota_final_en)->not->toBeNull()
        ->and($enrollment->resultadoSegunNotaFinal())->toBe('Aprobado');

    $this->assertDatabaseHas('notificaciones', [
        'user_id' => $admin->id,
        'tipo' => 'calificacion',
    ]);
});

test('the second partial cannot be registered before the first one', function () {
    $teacher = gradeTeacherProfile();
    $teacherUser = gradeTeacherActor($teacher);
    $enrollment = gradeEnrollment($teacher);

    $this->actingAs($teacherUser)
        ->from(route('gestion-academica.semestre', ['semestre' => 'Primero', 'gestion' => '2026-II']))
        ->patch(route('inscripciones.register-partial', $enrollment), [
            'parcial' => '2',
            'nota' => 70,
        ])
        ->assertSessionHasErrors('nota');

    $this->assertDatabaseHas('inscripciones', [
        'id' => $enrollment->id,
        'parcial_2' => null,
        'nota_final' => null,
    ]);
});

test('partial grades must stay within the zero to one hundred scale', function () {
    $teacher = gradeTeacherProfile();
    $teacherUser = gradeTeacherActor($teacher);
    $enrollment = gradeEnrollment($teacher);

    $this->actingAs($teacherUser)
        ->from(route('gestion-academica.semestre', ['semestre' => 'Primero', 'gestion' => '2026-II']))
        ->patch(route('inscripciones.register-partial', $enrollment), [
            'parcial' => '1',
            'nota' => 101,
        ])
        ->assertSessionHasErrors('nota');

    $this->actingAs($teacherUser)
        ->from(route('gestion-academica.semestre', ['semestre' => 'Primero', 'gestion' => '2026-II']))
        ->patch(route('inscripciones.register-partial', $enrollment), [
            'parcial' => '1',
            'nota' => -1,
        ])
        ->assertSessionHasErrors('nota');

    $this->assertDatabaseHas('inscripciones', [
        'id' => $enrollment->id,
        'parcial_1' => null,
    ]);
});

test('teachers cannot report a result before both partials are registered', function () {
    $teacher = gradeTeacherProfile();
    $teacherUser = gradeTeacherActor($teacher);
    $enrollment = gradeEnrollment($teacher, ['parcial_1' => 80, 'parcial_2' => null]);

    $this->actingAs($teacherUser)
        ->from(route('gestion-academica.semestre', ['semestre' => 'Primero', 'gestion' => '2026-II']))
        ->patch(route('inscripciones.report-result', $enrollment), [
            'resultado_reportado' => 'Aprobado',
        ])
        ->assertSessionHasErrors('resultado_reportado');

    $this->assertDatabaseHas('inscripciones', [
        'id' => $enrollment->id,
        'resultado_reportado' => null,
    ]);
});

test('teachers cannot report a result that contradicts the final grade', function () {
    $teacher = gradeTeacherProfile();
    $teacherUser = gradeTeacherActor($teacher);
    $enrollment = gradeEnrollment($teacher, ['parcial_1' => 40, 'parcial_2' => 50]);

    $this->actingAs($teacherUser)
        ->from(route('gestion-academica.semestre', ['semestre' => 'Primero', 'gestion' => '2026-II']))
        ->patch(route('inscripciones.report-result', $enrollment), [
            'resultado_reportado' => 'Aprobado',
        ])
        ->assertSessionHasErrors('resultado_reportado');

    $this->assertDatabaseHas('inscripciones', [
        'id' => $enrollment->id,
        'resultado_reportado' => null,
    ]);
});

test('teachers can report a result consistent with the final grade', function () {
    $admin = gradeAdminActor();
    $teacher = gradeTeacherProfile();
    $teacherUser = gradeTeacherActor($teacher);
    $enrollment = gradeEnrollment($teacher, ['parcial_1' => 80, 'parcial_2' => 90]);

    $this->actingAs($teacherUser)
        ->patch(route('inscripciones.report-result', $enrollment), [
            'resultado_reportado' => 'Aprobado',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('inscripciones', [
        'id' => $enrollment->id,
        'resultado_reportado' => 'Aprobado',
        'resultado_reportado_por' => $teacherUser->id,
        'estado' => 'Cursando',
    ]);

    $this->assertDatabaseHas('notificaciones', [
        'user_id' => $admin->id,
        'tipo' => 'resultado_reportado',
    ]);
});

test('administrators confirm the result and the teacher is notified', function () {
    $admin = gradeAdminActor();
    $teacher = gradeTeacherProfile();
    $teacherUser = gradeTeacherActor($teacher);
    $enrollment = gradeEnrollment($teacher, ['parcial_1' => 40, 'parcial_2' => 50]);
    $enrollment->update([
        'resultado_reportado' => 'Reprobado',
        'resultado_reportado_por' => $teacherUser->id,
        'fecha_reporte' => now(),
    ]);

    $this->actingAs($admin)
        ->patch(route('inscripciones.status', $enrollment), [
            'estado' => 'Reprobado',
        ])
        ->assertRedirect(route('gestion-academica.semestre', [
            'semestre' => 'Primero',
            'gestion' => '2026-II',
        ]));

    $this->assertDatabaseHas('inscripciones', [
        'id' => $enrollment->id,
        'estado' => 'Reprobado',
        'resultado_final' => 'Reprobado',
        'revisado_por' => $admin->id,
    ]);

    $this->assertDatabaseHas('notificaciones', [
        'user_id' => $teacherUser->id,
        'tipo' => 'decision_academica',
    ]);
});

test('teachers cannot grade enrollments from another teacher schedule', function () {
    $teacher = gradeTeacherProfile();
    $otherTeacher = gradeTeacherProfile();
    $otherTeacherUser = gradeTeacherActor($otherTeacher);
    $enrollment = gradeEnrollment($teacher);

    $this->actingAs($otherTeacherUser)
        ->patch(route('inscripciones.register-partial', $enrollment), [
            'parcial' => '1',
            'nota' => 70,
        ])
        ->assertNotFound();
});

function gradeAdminActor(): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'administrador',
        'activo' => true,
    ]);
}

function gradeTeacherProfile(): Docente
{
    return Docente::create([
        'nombres' => 'Docente',
        'apellido_paterno' => 'Parcial',
        'apellido_materno' => fake()->lastName(),
        'ci' => fake()->unique()->numerify('########'),
        'cargo' => 'Docente',
        'estado' => 'activo',
    ]);
}

function gradeTeacherActor(Docente $docente): User
{
    return User::create([
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'docente',
        'docente_id' => $docente->id,
        'activo' => true,
    ]);
}

/**
 * @param  array{parcial_1?: int|null, parcial_2?: int|null}  $notas
 */
function gradeEnrollment(Docente $docente, array $notas = []): Inscripcion
{
    $suffix = fake()->unique()->numerify('####');

    $estudiante = Estudiante::create([
        'nombres' => 'Estudiante',
        'apellido_paterno' => 'Parcial',
        'apellido_materno' => 'Prueba',
        'matricula' => "MAT{$suffix}",
        'semestre' => 'Primero',
        'ci' => "CI{$suffix}",
        'estado' => 'activo',
    ]);

    $materia = Materia::create([
        'nombre' => "Materia {$suffix}",
        'sigla' => "MAT-{$suffix}",
        'semestre' => 'Primero',
        'estado' => 'activo',
    ]);

    Horario::create([
        'docente_id' => $docente->id,
        'materia_id' => $materia->id,
        'dia_semana' => 'Lunes',
        'hora_inicio' => '08:00',
        'hora_fin' => '10:00',
        'estado' => 'activo',
    ]);

    $atributos = [
        'estudiante_id' => $estudiante->id,
        'materia_id' => $materia->id,
        'gestion' => '2026-II',
        'tipo' => 'Normal',
        'estado' => 'Cursando',
        'fecha_inscripcion' => '2026-10-14',
    ];

    if (array_key_exists('parcial_1', $notas)) {
        $atributos['parcial_1'] = $notas['parcial_1'];
    }

    if (array_key_exists('parcial_2', $notas)) {
        $atributos['parcial_2'] = $notas['parcial_2'];
    }

    $inscripcion = Inscripcion::create($atributos);

    if ($inscripcion->tieneAmbosParciales()) {
        $inscripcion->update([
            'nota_final' => $inscripcion->calcularNotaFinal(),
            'nota_final_en' => now(),
        ]);
    }

    return $inscripcion;
}

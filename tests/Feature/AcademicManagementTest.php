<?php

use App\Models\ConfiguracionAcademica;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

test('administrators can manage subjects schedules and enrollments', function () {
    $admin = academicAdminActor();
    $teacher = academicTeacherProfile();
    $student = academicStudentProfile();

    $this->actingAs($admin)
        ->post(route('materias.store'), [
            'nombre' => 'Programación Industrial',
            'sigla' => 'PROG-I',
            'semestre' => 'Primero',
            'gestion' => '2026-II',
        ])
        ->assertRedirect(route('gestion-academica.semestre', ['semestre' => 'Primero', 'gestion' => '2026-II']));

    $materia = Materia::where('nombre', 'Programación Industrial')->firstOrFail();
    $this->actingAs($admin)
        ->put(route('materias.update', $materia), [
            'nombre' => 'Programación Industrial II',
            'sigla' => 'PROG-II',
            'semestre' => 'Segundo',
            'gestion' => '2026-II',
        ])
        ->assertRedirect(route('gestion-academica.semestre', ['semestre' => 'Segundo', 'gestion' => '2026-II']));

    $this->assertDatabaseHas('materias', [
        'id' => $materia->id,
        'nombre' => 'Programación Industrial II',
        'sigla' => 'PROG-II',
        'semestre' => 'Segundo',
    ]);

    $this->actingAs($admin)
        ->get(route('gestion-academica.semestre', ['semestre' => 'Segundo', 'gestion' => '2026-II']))
        ->assertSeeText('PROG-II');

    $this->actingAs($admin)
        ->post(route('horarios.store'), [
            'docente_id' => $teacher->id,
            'materia_id' => $materia->id,
            'dia_semana' => 'Lunes',
            'hora_inicio' => '08:00',
            'hora_fin' => '10:00',
            'aula' => 'Lab 1',
            'gestion' => '2026-II',
        ])
        ->assertRedirect(route('gestion-academica.semestre', ['semestre' => 'Segundo', 'gestion' => '2026-II']));

    $horario = Horario::where('materia_id', $materia->id)->firstOrFail();
    $this->actingAs($admin)
        ->patch(route('horarios.status', $horario), ['estado' => 'inactivo', 'gestion' => '2026-II'])
        ->assertRedirect(route('gestion-academica.semestre', ['semestre' => 'Segundo', 'gestion' => '2026-II']));
    $this->assertDatabaseHas('horarios', [
        'id' => $horario->id,
        'estado' => 'inactivo',
    ]);

    $this->actingAs($admin)
        ->post(route('inscripciones.store'), [
            'estudiante_id' => $student->id,
            'materia_id' => $materia->id,
            'gestion' => '2026-II',
            'tipo' => 'Normal',
            'fecha_inscripcion' => '2026-10-14',
        ])
        ->assertRedirect(route('gestion-academica.semestre', ['semestre' => 'Segundo', 'gestion' => '2026-II']));

    $inscripcion = Inscripcion::where('estudiante_id', $student->id)->firstOrFail();
    $this->actingAs($admin)
        ->patch(route('inscripciones.status', $inscripcion), ['estado' => 'Inactivo'])
        ->assertRedirect(route('gestion-academica.semestre', ['semestre' => 'Segundo', 'gestion' => '2026-II']));
    $this->assertDatabaseHas('inscripciones', [
        'id' => $inscripcion->id,
        'estado' => 'Inactivo',
    ]);
});

test('administrators cannot assign a sigla already used by another subject', function () {
    $admin = academicAdminActor();
    $existingSubject = Materia::create([
        'nombre' => 'Programación Industrial',
        'sigla' => 'PROG-I',
        'semestre' => 'Primero',
        'estado' => 'activo',
    ]);

    $this->actingAs($admin)
        ->from(route('materias.create', ['semestre' => 'Primero']))
        ->post(route('materias.store'), [
            'nombre' => 'Programación Aplicada',
            'sigla' => 'PROG-I',
            'semestre' => 'Primero',
        ])
        ->assertSessionHasErrors('sigla');

    $this->assertDatabaseCount('materias', 1);
    $this->assertDatabaseHas('materias', [
        'id' => $existingSubject->id,
        'sigla' => 'PROG-I',
    ]);
});

test('administrators cannot create or update schedules for weekends', function () {
    $admin = academicAdminActor();
    $teacher = academicTeacherProfile();
    $subject = academicSubject('Automatización Industrial');
    $schedule = academicSchedule($teacher, $subject);

    $this->actingAs($admin)
        ->get(route('horarios.create'))
        ->assertSeeText('Lunes')
        ->assertSeeText('Viernes')
        ->assertDontSeeText('Sabado')
        ->assertDontSeeText('Domingo');

    $this->actingAs($admin)
        ->from(route('horarios.create'))
        ->post(route('horarios.store'), [
            'docente_id' => $teacher->id,
            'materia_id' => $subject->id,
            'dia_semana' => 'Sabado',
            'hora_inicio' => '08:00',
            'hora_fin' => '10:00',
        ])
        ->assertSessionHasErrors('dia_semana');

    $this->actingAs($admin)
        ->from(route('horarios.edit', $schedule))
        ->put(route('horarios.update', $schedule), [
            'docente_id' => $teacher->id,
            'materia_id' => $subject->id,
            'dia_semana' => 'Domingo',
            'hora_inicio' => '08:00',
            'hora_fin' => '10:00',
        ])
        ->assertSessionHasErrors('dia_semana');

    $this->assertDatabaseCount('horarios', 1);
    $this->assertDatabaseHas('horarios', [
        'id' => $schedule->id,
        'dia_semana' => 'Lunes',
    ]);
});

test('teachers can only view academic records in their assigned schedules', function () {
    $teacher = academicTeacherProfile();
    $otherTeacher = academicTeacherProfile();
    $teacherUser = academicTeacherActor($teacher);
    $student = academicStudentProfile();
    $otherStudent = academicStudentProfile();
    $assignedSubject = academicSubject('Control Industrial');
    $otherSubject = academicSubject('Electrónica Avanzada');

    $assignedSchedule = academicSchedule($teacher, $assignedSubject);
    academicSchedule($otherTeacher, $otherSubject);
    academicEnrollment($student, $assignedSubject);
    academicEnrollment($otherStudent, $otherSubject);

    $this->actingAs($teacherUser)
        ->get(route('gestion-academica.semestre', ['semestre' => 'Primero', 'gestion' => '2026-II']))
        ->assertOk()
        ->assertSeeText('Control Industrial')
        ->assertSeeText($student->matricula)
        ->assertDontSeeText('Electrónica Avanzada')
        ->assertDontSeeText($otherStudent->matricula);

    $this->actingAs($teacherUser)
        ->get(route('materias.create', ['semestre' => 'Primero']))
        ->assertForbidden();

    $this->actingAs($teacherUser)
        ->patch(route('horarios.status', $assignedSchedule), ['estado' => 'inactivo'])
        ->assertForbidden();

    $this->actingAs($teacherUser)
        ->patch(route('inscripciones.register-partial', $student->inscripciones()->firstOrFail()), [
            'parcial' => '1',
            'nota' => 80,
        ])
        ->assertRedirect();

    $this->actingAs($teacherUser)
        ->patch(route('inscripciones.register-partial', $student->inscripciones()->firstOrFail()), [
            'parcial' => '2',
            'nota' => 90,
        ])
        ->assertRedirect();

    $this->actingAs($teacherUser)
        ->patch(route('inscripciones.report-result', $student->inscripciones()->firstOrFail()), [
            'resultado_reportado' => 'Aprobado',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('inscripciones', [
        'estudiante_id' => $student->id,
        'resultado_reportado' => 'Aprobado',
        'resultado_reportado_por' => $teacherUser->id,
        'estado' => 'Cursando',
    ]);

    $otherTeacherUser = academicTeacherActor($otherTeacher);
    $this->actingAs($otherTeacherUser)
        ->patch(route('inscripciones.report-result', $student->inscripciones()->firstOrFail()), [
            'resultado_reportado' => 'Reprobado',
        ])
        ->assertNotFound();

    $admin = academicAdminActor();
    $inscripcion = $student->inscripciones()->firstOrFail();
    $this->actingAs($admin)
        ->patch(route('inscripciones.status', $inscripcion), ['estado' => 'Aprobado'])
        ->assertRedirect(route('gestion-academica.semestre', ['semestre' => 'Primero', 'gestion' => '2026-II']));

    $this->assertDatabaseHas('inscripciones', [
        'id' => $inscripcion->id,
        'estado' => 'Aprobado',
        'revisado_por' => $admin->id,
    ]);
    expect($student->fresh()->semestre)->toBe('Primero');
});

test('an administrator cannot create duplicate enrollments for a student and subject', function () {
    $admin = academicAdminActor();
    $student = academicStudentProfile();
    $subject = academicSubject('Redes Industriales');
    academicEnrollment($student, $subject);

    $this->actingAs($admin)
        ->from(route('inscripciones.create'))
        ->post(route('inscripciones.store'), [
            'estudiante_id' => $student->id,
            'materia_id' => $subject->id,
            'gestion' => '2026-II',
            'tipo' => 'Normal',
            'fecha_inscripcion' => '2026-10-14',
        ])
        ->assertSessionHasErrors([
            'materia_id' => 'El estudiante ya tiene una inscripción registrada en esta materia durante esta gestión.',
        ]);

    $this->actingAs($admin)
        ->post(route('inscripciones.store'), [
            'estudiante_id' => $student->id,
            'materia_id' => $subject->id,
            'gestion' => '2027-I',
            'tipo' => 'Arrastre',
            'fecha_inscripcion' => '2027-02-01',
        ])
        ->assertRedirect(route('gestion-academica.semestre', [
            'semestre' => 'Primero',
            'gestion' => '2027-I',
        ]));

    $this->assertDatabaseHas('inscripciones', [
        'estudiante_id' => $student->id,
        'materia_id' => $subject->id,
        'gestion' => '2027-I',
        'tipo' => 'Arrastre',
        'estado' => 'Cursando',
    ]);
});

test('administrators can change enrollment status from the edit form after a teacher reports a result', function () {
    $admin = academicAdminActor();
    $student = academicStudentProfile();
    $subject = academicSubject('Redes Industriales');
    $enrollment = academicEnrollment($student, $subject);
    $enrollment->update([
        'resultado_reportado' => 'Aprobado',
        'resultado_reportado_por' => $admin->id,
        'fecha_reporte' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('inscripciones.edit', $enrollment))
        ->assertSeeText('Estado académico')
        ->assertSee('name="estado"', false);

    $this->actingAs($admin)
        ->put(route('inscripciones.update', $enrollment), [
            'estudiante_id' => $student->id,
            'materia_id' => $subject->id,
            'gestion' => '2026-II',
            'tipo' => 'Normal',
            'fecha_inscripcion' => '2026-10-14',
            'estado' => 'Aprobado',
        ])
        ->assertRedirect(route('gestion-academica.semestre', [
            'semestre' => 'Primero',
            'gestion' => '2026-II',
        ]))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('inscripciones', [
        'id' => $enrollment->id,
        'estado' => 'Aprobado',
        'revisado_por' => $admin->id,
    ]);
});

test('administrators cannot approve an enrollment from edit before the teacher reports a result', function () {
    $admin = academicAdminActor();
    $student = academicStudentProfile();
    $subject = academicSubject('Redes Industriales');
    $enrollment = academicEnrollment($student, $subject);

    $this->actingAs($admin)
        ->from(route('inscripciones.edit', $enrollment))
        ->put(route('inscripciones.update', $enrollment), [
            'estudiante_id' => $student->id,
            'materia_id' => $subject->id,
            'gestion' => '2026-II',
            'tipo' => 'Normal',
            'fecha_inscripcion' => '2026-10-14',
            'estado' => 'Aprobado',
        ])
        ->assertSessionHasErrors([
            'estado' => 'El docente debe reportar el resultado antes de la revisión administrativa.',
        ]);

    $this->assertDatabaseHas('inscripciones', [
        'id' => $enrollment->id,
        'estado' => 'Cursando',
        'revisado_por' => null,
    ]);
});

test('guests cannot view academic management', function () {
    $this->get(route('gestion-academica.index'))->assertRedirect(route('login'));
});

test('administrators can change the global academic period without rewriting enrollment history', function () {
    $admin = academicAdminActor();
    $student = academicStudentProfile();
    $subject = academicSubject('Programación Industrial');
    $enrollment = academicEnrollment($student, $subject);
    ConfiguracionAcademica::query()->findOrFail(1)->update(['gestion_vigente' => '2026-II']);

    $this->actingAs($admin)
        ->patch(route('gestion-academica.gestion-vigente'), [
            'gestion_vigente' => '2027-I',
            'semestre' => 'Primero',
        ])
        ->assertRedirect(route('gestion-academica.semestre', ['semestre' => 'Primero']))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('configuracion_academica', [
        'id' => 1,
        'gestion_vigente' => '2027-I',
    ]);
    $this->assertDatabaseHas('inscripciones', [
        'id' => $enrollment->id,
        'gestion' => '2026-II',
    ]);

    $this->actingAs($admin)
        ->get(route('gestion-academica.semestre', 'Primero'))
        ->assertOk()
        ->assertSeeText('2027-I')
        ->assertSeeText('Volver al listado')
        ->assertSeeText('Actualizar para todos')
        ->assertDontSeeText('Ver gestión')
        ->assertSee('onchange="this.form.submit()"', false);
});

test('teachers cannot change the global academic period', function () {
    $teacher = academicTeacherActor(academicTeacherProfile());
    ConfiguracionAcademica::query()->findOrFail(1)->update(['gestion_vigente' => '2026-II']);

    $this->actingAs($teacher)
        ->patch(route('gestion-academica.gestion-vigente'), [
            'gestion_vigente' => '2027-I',
            'semestre' => 'Primero',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('configuracion_academica', [
        'id' => 1,
        'gestion_vigente' => '2026-II',
    ]);
});

test('new enrollment forms default to the configured global academic period', function () {
    $admin = academicAdminActor();
    ConfiguracionAcademica::query()->findOrFail(1)->update(['gestion_vigente' => '2027-I']);

    $this->actingAs($admin)
        ->get(route('inscripciones.create'))
        ->assertOk()
        ->assertSee('value="2027-I"', false);
});

test('administrators cannot save an invalid global academic period', function () {
    $admin = academicAdminActor();
    ConfiguracionAcademica::query()->findOrFail(1)->update(['gestion_vigente' => '2026-II']);

    $this->actingAs($admin)
        ->from(route('gestion-academica.semestre', 'Primero'))
        ->patch(route('gestion-academica.gestion-vigente'), [
            'gestion_vigente' => '2027-III',
            'semestre' => 'Primero',
        ])
        ->assertSessionHasErrors('gestion_vigente');

    $this->assertDatabaseHas('configuracion_academica', [
        'id' => 1,
        'gestion_vigente' => '2026-II',
    ]);
});

test('the academic landing page displays only the three laboratory semesters', function () {
    $admin = academicAdminActor();

    $this->actingAs($admin)
        ->get(route('gestion-academica.index', ['gestion' => '2026-I']))
        ->assertOk()
        ->assertSeeText('Primero semestre')
        ->assertSeeText('Segundo semestre')
        ->assertSeeText('Tercero semestre')
        ->assertSeeText('Gestión vigente')
        ->assertSee(route('dashboard'), false)
        ->assertDontSee('name="gestion"', false)
        ->assertDontSeeText('Consultar')
        ->assertDontSeeText('Cuarto semestre');
});

function academicAdminActor(): User
{
    return User::create([
        'name' => 'Administrador académico',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'administrador',
        'docente_id' => academicTeacherProfile()->id,
    ]);
}

function academicTeacherActor(Docente $docente): User
{
    return User::create([
        'name' => 'Docente académico',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'docente',
        'docente_id' => $docente->id,
    ]);
}

function academicTeacherProfile(): Docente
{
    return Docente::create([
        'nombres' => 'Docente',
        'apellido_paterno' => 'Académico',
        'apellido_materno' => fake()->lastName(),
        'ci' => fake()->unique()->numerify('########'),
        'cargo' => 'Docente',
        'estado' => 'activo',
    ]);
}

function academicStudentProfile(): Estudiante
{
    return Estudiante::create([
        'nombres' => 'Estudiante',
        'apellido_paterno' => 'Académico',
        'apellido_materno' => fake()->lastName(),
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 'Primero',
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
}

function academicSubject(string $name): Materia
{
    return Materia::create([
        'nombre' => $name,
        'semestre' => 'Primero',
        'estado' => 'activo',
    ]);
}

function academicSchedule(Docente $docente, Materia $materia): Horario
{
    return Horario::create([
        'docente_id' => $docente->id,
        'materia_id' => $materia->id,
        'dia_semana' => 'Lunes',
        'hora_inicio' => '08:00',
        'hora_fin' => '10:00',
        'estado' => 'activo',
    ]);
}

function academicEnrollment(Estudiante $estudiante, Materia $materia): Inscripcion
{
    return Inscripcion::create([
        'estudiante_id' => $estudiante->id,
        'materia_id' => $materia->id,
        'gestion' => '2026-II',
        'tipo' => 'Normal',
        'estado' => 'Cursando',
        'fecha_inscripcion' => '2026-10-14',
    ]);
}

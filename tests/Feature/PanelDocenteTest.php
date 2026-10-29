<?php

use App\Models\Accesorio;
use App\Models\Asignacion;
use App\Models\ConfiguracionAcademica;
use App\Models\Diagnostico;
use App\Models\Docente;
use App\Models\Equipo;
use App\Models\Estudiante;
use App\Models\Heartbeat;
use App\Models\Horario;
use App\Models\Incidencia;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

beforeEach(function (): void {
    $this->withoutVite();
});

test('teachers see only current students enrolled in their active subjects', function (): void {
    $teacher = teacherPanelUser();
    $otherTeacher = teacherPanelUser('Other');
    ConfiguracionAcademica::query()->findOrFail(1)->update(['gestion_vigente' => '2026-II']);
    $subject = teacherPanelSubject('Automation');
    $otherSubject = teacherPanelSubject('Networks');
    teacherPanelSchedule($teacher, $subject);
    teacherPanelSchedule($otherTeacher, $otherSubject);

    $currentStudent = teacherPanelStudent('Current Student');
    $otherTeacherStudent = teacherPanelStudent('Other Teacher Student');
    $inactiveStudent = teacherPanelStudent('Inactive Student');
    $historicStudent = teacherPanelStudent('Historic Student');

    teacherPanelEnrollment($currentStudent, $subject, '2026-II');
    teacherPanelEnrollment($otherTeacherStudent, $otherSubject, '2026-II');
    teacherPanelEnrollment($inactiveStudent, $subject, '2026-II', 'Inactivo');
    teacherPanelEnrollment($historicStudent, $subject, '2026-I');

    $this->actingAs($teacher)
        ->get(route('panel-docente.estudiantes'))
        ->assertOk()
        ->assertSeeText('Current Student')
        ->assertSeeText('Automation')
        ->assertDontSeeText('Other Teacher Student')
        ->assertDontSeeText('Networks')
        ->assertDontSeeText('Inactive Student')
        ->assertDontSeeText('Historic Student')
        ->assertDontSeeText($currentStudent->ci)
        ->assertDontSeeText($currentStudent->telefono);
});

test('the academic panel shows colleague profiles without account details', function (): void {
    $teacher = teacherPanelUser('Current');
    $colleague = teacherPanelProfile('Colleague');
    $administrativeProfile = teacherPanelProfile('Administrative');
    $colleagueUser = User::create([
        'name' => 'Private colleague account name',
        'username' => 'private-colleague-login',
        'password' => 'private-colleague-password',
        'tipo_usuario' => 'docente',
        'docente_id' => $colleague->id,
    ]);
    User::create([
        'name' => 'Administrative account',
        'username' => 'administrative-account',
        'password' => 'password',
        'tipo_usuario' => 'administrador',
        'docente_id' => $administrativeProfile->id,
    ]);
    $colleague->update([
        'telefono' => '70012345',
        'cargo' => 'Docente de laboratorio',
        'nivel_academico' => 'Licenciatura',
        'fecha_ingreso' => '2020-02-03',
    ]);

    $this->actingAs($teacher)
        ->get(route('panel-docente.academico'))
        ->assertOk()
        ->assertSeeText('Colleague')
        ->assertSeeText($colleague->ci)
        ->assertSeeText('70012345')
        ->assertSeeText('Docente de laboratorio')
        ->assertSeeText('Licenciatura')
        ->assertSeeText('03/02/2020')
        ->assertDontSeeText('Administrative')
        ->assertDontSeeText($colleagueUser->username)
        ->assertDontSeeText($colleagueUser->name)
        ->assertDontSeeText('private-colleague-password');
});

test('administrator and student accounts cannot access teacher-only panels', function (string $role): void {
    $user = User::create([
        'name' => "Restricted {$role}",
        'username' => "restricted-{$role}",
        'password' => 'password',
        'tipo_usuario' => $role,
    ]);

    $this->actingAs($user)
        ->get(route('panel-docente.estudiantes'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('panel-docente.academico'))
        ->assertForbidden();
})->with([
    'administrator' => 'administrador',
    'student' => 'estudiante',
]);

test('guests are redirected from teacher-only panels', function (): void {
    $this->get(route('panel-docente.estudiantes'))
        ->assertRedirect(route('login'));

    $this->get(route('panel-docente.academico'))
        ->assertRedirect(route('login'));
});

test('the teacher dashboard renders its modules without a users section', function (): void {
    $teacher = teacherPanelUser();

    $this->withoutMiddleware(EnsureEmailIsVerified::class)
        ->actingAs($teacher)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Panel docente')
        ->assertSeeText('Panel Estudiantil')
        ->assertSeeText('Panel Académico')
        ->assertSeeText('Monitoreo de mis equipos')
        ->assertSeeText('Diagnósticos críticos')
        ->assertSeeText('Mi cuenta')
        ->assertSeeText('Preguntas de seguridad')
        ->assertDontSeeText('Personas y usuarios');

    $this->actingAs($teacher)
        ->get(route('usuarios.index'))
        ->assertForbidden();
});

test('teachers only see diagnostics and monitoring for equipment assigned through their schedules', function (): void {
    $teacher = teacherPanelUser();
    $otherTeacher = teacherPanelUser('Other');
    $subject = teacherPanelSubject('Automation');
    $otherSubject = teacherPanelSubject('Networks');
    $schedule = teacherPanelSchedule($teacher, $subject);
    $otherSchedule = teacherPanelSchedule($otherTeacher, $otherSubject);
    $student = teacherPanelStudent('Assigned Student');
    $ownEquipment = teacherPanelEquipment('OWN-001');
    $otherEquipment = teacherPanelEquipment('OTHER-001');
    teacherPanelAssignment($ownEquipment, $student, $subject, $schedule);
    teacherPanelAssignment($otherEquipment, $student, $otherSubject, $otherSchedule);
    $ownHeartbeat = teacherPanelHeartbeat($ownEquipment);
    $otherHeartbeat = teacherPanelHeartbeat($otherEquipment);
    teacherPanelDiagnostic($ownEquipment, $ownHeartbeat, 'Teacher-visible diagnostic');
    teacherPanelDiagnostic($otherEquipment, $otherHeartbeat, 'Private other-teacher diagnostic');

    $this->actingAs($teacher)
        ->get(route('monitoreo.index'))
        ->assertOk()
        ->assertSeeText('OWN-001')
        ->assertDontSeeText('OTHER-001');

    $this->actingAs($teacher)
        ->get(route('diagnosticos.index'))
        ->assertOk()
        ->assertSeeText('Teacher-visible diagnostic')
        ->assertDontSeeText('Private other-teacher diagnostic')
        ->assertDontSeeText('OTHER-001');
});

test('teacher reports include only resources linked to their scheduled assignments', function (): void {
    $teacher = teacherPanelUser();
    $otherTeacher = teacherPanelUser('Other');
    $subject = teacherPanelSubject('Automation');
    $otherSubject = teacherPanelSubject('Networks');
    $schedule = teacherPanelSchedule($teacher, $subject);
    $otherSchedule = teacherPanelSchedule($otherTeacher, $otherSubject);
    $student = teacherPanelStudent('Assigned Student');
    $ownEquipment = teacherPanelEquipment('OWN-001');
    $otherEquipment = teacherPanelEquipment('OTHER-001');
    $unassignedEquipment = teacherPanelEquipment('UNASSIGNED-001');
    $ownAssignment = teacherPanelAssignment($ownEquipment, $student, $subject, $schedule);
    $otherAssignment = teacherPanelAssignment($otherEquipment, $student, $otherSubject, $otherSchedule);
    teacherPanelAccessory($ownEquipment);
    teacherPanelAccessory($otherEquipment);
    teacherPanelAccessory($unassignedEquipment);
    teacherPanelIncident($ownEquipment, $ownAssignment);
    teacherPanelIncident($otherEquipment, $otherAssignment);

    $this->actingAs($teacher)
        ->get(route('reportes.index'))
        ->assertOk()
        ->assertViewHas('estadisticas', function (array $estadisticas): bool {
            return $estadisticas['totalEquipos'] === 1
                && $estadisticas['totalAccesorios'] === 1
                && $estadisticas['totalAsignaciones'] === 1
                && $estadisticas['totalIncidencias'] === 1;
        })
        ->assertDontSeeText('OTHER-001')
        ->assertDontSeeText('UNASSIGNED-001');
});

function teacherPanelUser(string $suffix = 'Current'): User
{
    $profile = teacherPanelProfile($suffix);

    return User::create([
        'name' => "Teacher {$suffix}",
        'username' => strtolower("teacher-{$suffix}"),
        'password' => 'password',
        'tipo_usuario' => 'docente',
        'docente_id' => $profile->id,
    ]);
}

function teacherPanelProfile(string $suffix): Docente
{
    return Docente::create([
        'nombres' => $suffix,
        'apellido_paterno' => 'Teacher',
        'apellido_materno' => 'Profile',
        'ci' => "CI-{$suffix}",
        'cargo' => 'Docente',
        'estado' => 'activo',
    ]);
}

function teacherPanelStudent(string $name): Estudiante
{
    return Estudiante::create([
        'nombres' => $name,
        'apellido_paterno' => 'Student',
        'apellido_materno' => 'Profile',
        'matricula' => strtoupper(str_replace(' ', '-', $name)),
        'semestre' => 'Primero',
        'ci' => 'STU-'.strtoupper(str_replace(' ', '-', $name)),
        'telefono' => '71112222',
        'estado' => 'activo',
    ]);
}

function teacherPanelSubject(string $name): Materia
{
    return Materia::create([
        'nombre' => $name,
        'sigla' => strtoupper(substr($name, 0, 4)),
        'semestre' => 'Primero',
        'estado' => 'activo',
    ]);
}

function teacherPanelSchedule(User $teacher, Materia $subject): Horario
{
    return Horario::create([
        'docente_id' => $teacher->docente_id,
        'materia_id' => $subject->id,
        'dia_semana' => 'Lunes',
        'hora_inicio' => '08:00',
        'hora_fin' => '10:00',
        'estado' => 'activo',
    ]);
}

function teacherPanelEnrollment(
    Estudiante $student,
    Materia $subject,
    string $gestion,
    string $estado = 'Cursando',
): Inscripcion {
    return Inscripcion::create([
        'estudiante_id' => $student->id,
        'materia_id' => $subject->id,
        'gestion' => $gestion,
        'tipo' => 'Normal',
        'estado' => $estado,
        'fecha_inscripcion' => now()->toDateString(),
    ]);
}

function teacherPanelEquipment(string $code): Equipo
{
    return Equipo::create([
        'codigo' => $code,
        'tipo_equipo' => 'Computadora',
        'marca' => 'Laboratorio',
        'modelo' => 'Equipo de prueba',
        'modo_uso' => 'Laboratorio',
        'estado' => 'disponible',
    ]);
}

function teacherPanelAssignment(
    Equipo $equipment,
    Estudiante $student,
    Materia $subject,
    Horario $schedule,
): Asignacion {
    return Asignacion::create([
        'equipo_id' => $equipment->id,
        'estudiante_id' => $student->id,
        'materia_id' => $subject->id,
        'horario_id' => $schedule->id,
        'origen' => 'presencial',
        'fecha_inicio' => now(),
        'estado' => 'activa',
    ]);
}

function teacherPanelAccessory(Equipo $equipment): Accesorio
{
    return Accesorio::create([
        'equipo_id' => $equipment->id,
        'nombre' => 'Teclado de prueba',
        'cantidad' => 1,
        'estado' => 'disponible',
    ]);
}

function teacherPanelIncident(Equipo $equipment, Asignacion $assignment): Incidencia
{
    return Incidencia::create([
        'equipo_id' => $equipment->id,
        'asignacion_id' => $assignment->id,
        'tipo' => 'daño',
        'descripcion' => 'Incidencia de prueba',
        'prioridad' => 'media',
        'estado' => 'pendiente',
        'fecha_reporte' => now(),
    ]);
}

function teacherPanelHeartbeat(Equipo $equipment): Heartbeat
{
    return Heartbeat::create([
        'equipo_id' => $equipment->id,
        'fecha_hora' => now(),
    ]);
}

function teacherPanelDiagnostic(Equipo $equipment, Heartbeat $heartbeat, string $description): Diagnostico
{
    return Diagnostico::create([
        'equipo_id' => $equipment->id,
        'heartbeat_id' => $heartbeat->id,
        'tipo' => 'salud',
        'nivel' => 'Crítico',
        'descripcion' => $description,
        'fecha_hora' => now(),
    ]);
}

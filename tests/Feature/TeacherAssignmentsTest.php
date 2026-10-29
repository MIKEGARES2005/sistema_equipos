<?php

use App\Models\Asignacion;
use App\Models\Docente;
use App\Models\Equipo;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Incidencia;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('docentes can assign equipment to an actively enrolled student in their own class', function () {
    $context = createTeacherAssignmentContext();

    $response = $this->actingAs($context['teacher'])->post(route('asignaciones.store'), [
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'horario_id' => $context['horario']->id,
        'fecha_inicio' => '2026-10-14 09:00:00',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('asignaciones.index'));

    $this->assertDatabaseHas('asignaciones', [
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $context['materia']->id,
        'horario_id' => $context['horario']->id,
        'estado' => 'activa',
    ]);

    $this->assertDatabaseHas('equipos', [
        'id' => $context['equipo']->id,
        'estado' => 'En uso',
    ]);
});

test('docentes only see matching assignments from their own schedules and selected state', function () {
    $context = createTeacherAssignmentContext();
    $context['equipo']->update(['codigo' => 'EQ-PROPIO-001']);

    Asignacion::create([
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $context['materia']->id,
        'horario_id' => $context['horario']->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-14 09:00:00',
        'estado' => 'ACTIVA',
    ]);
    $estudianteHistorico = Estudiante::create([
        'nombres' => 'Estudiante Historico',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Sistema',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 'Primero',
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    Asignacion::create([
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $estudianteHistorico->id,
        'materia_id' => $context['materia']->id,
        'horario_id' => $context['horario']->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-13 09:00:00',
        'fecha_fin' => '2026-10-13 10:00:00',
        'estado' => 'FINALIZADA',
    ]);

    $otroDocente = createAssignmentDocente();
    $otraMateria = Materia::create([
        'nombre' => 'Materia de otro docente',
        'semestre' => 'Segundo',
        'estado' => 'activo',
    ]);
    $otroHorario = Horario::create([
        'docente_id' => $otroDocente->id,
        'materia_id' => $otraMateria->id,
        'dia_semana' => 'Martes',
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
        'aula' => 'Aula 2',
        'estado' => 'activo',
    ]);
    $otroEstudiante = Estudiante::create([
        'nombres' => 'Estudiante Ajeno',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Sistema',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 'Segundo',
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $otroEquipo = Equipo::create([
        'codigo' => 'EQ-AJENO-001',
        'tipo_equipo' => 'Laptop',
        'marca' => 'Marca',
        'modelo' => 'Modelo ajeno',
        'modo_uso' => 'individual',
        'estado' => 'En uso',
    ]);
    Asignacion::create([
        'equipo_id' => $otroEquipo->id,
        'estudiante_id' => $otroEstudiante->id,
        'materia_id' => $otraMateria->id,
        'horario_id' => $otroHorario->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-14 09:00:00',
        'estado' => 'activa',
    ]);

    $this->actingAs($context['teacher'])
        ->get(route('asignaciones.index', [
            'buscar' => 'Laptop',
            'estado' => 'activa',
        ]))
        ->assertSeeText('EQ-PROPIO-001')
        ->assertSeeText('Activa')
        ->assertDontSeeText('Estudiante Historico')
        ->assertDontSeeText('Estudiante Ajeno');

    $this->actingAs($context['teacher'])
        ->get(route('asignaciones.index', [
            'buscar' => 'Laptop',
            'estado' => 'finalizada',
        ]))
        ->assertSeeText('EQ-PROPIO-001')
        ->assertSeeText('Finalizada')
        ->assertSeeText('Estudiante Historico')
        ->assertDontSeeText('Estudiante Ajeno');

    $this->actingAs($context['teacher'])
        ->get(route('dashboard'))
        ->assertSeeText('Asignaciones recientes')
        ->assertSeeText('EQ-PROPIO-001')
        ->assertDontSeeText('EQ-AJENO-001');
});

test('administrators see recent assignments on the dashboard', function () {
    $context = createTeacherAssignmentContext();
    $context['equipo']->update(['codigo' => 'EQ-DASH-001']);
    Asignacion::create([
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $context['materia']->id,
        'horario_id' => $context['horario']->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-14 09:00:00',
        'estado' => 'activa',
    ]);
    $adminDocente = createAssignmentDocente();
    $admin = User::create([
        'name' => 'Administrador de prueba',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'administrador',
        'docente_id' => $adminDocente->id,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertSeeText('Asignaciones recientes')
        ->assertSeeText('EQ-DASH-001')
        ->assertSeeText('Estudiante Prueba Sistema');
});

test('teachers see only their activity within the selected report period', function () {
    $context = createTeacherAssignmentContext();
    $context['equipo']->update(['codigo' => 'EQ-REPORTE-DOCENTE']);
    $asignacionEnPeriodo = Asignacion::create([
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $context['materia']->id,
        'horario_id' => $context['horario']->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-14 09:00:00',
        'estado' => 'activa',
    ]);
    $asignacionFueraDelPeriodo = Asignacion::create([
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $context['materia']->id,
        'horario_id' => $context['horario']->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-13 09:00:00',
        'estado' => 'finalizada',
    ]);
    Incidencia::create([
        'equipo_id' => $context['equipo']->id,
        'asignacion_id' => $asignacionEnPeriodo->id,
        'tipo' => 'Hardware',
        'descripcion' => 'Incidencia propia del periodo',
        'prioridad' => 'Media',
        'estado' => 'Reportada',
        'fecha_reporte' => '2026-10-14 10:00:00',
    ]);
    Incidencia::create([
        'equipo_id' => $context['equipo']->id,
        'asignacion_id' => $asignacionFueraDelPeriodo->id,
        'tipo' => 'Software',
        'descripcion' => 'Incidencia fuera del periodo',
        'prioridad' => 'Baja',
        'estado' => 'Resuelta',
        'fecha_reporte' => '2026-10-13 10:00:00',
    ]);

    $otroDocente = createAssignmentDocente();
    $otraMateria = Materia::create([
        'nombre' => 'Materia ajena al reporte',
        'semestre' => 'Segundo',
        'estado' => 'activo',
    ]);
    $otroHorario = Horario::create([
        'docente_id' => $otroDocente->id,
        'materia_id' => $otraMateria->id,
        'dia_semana' => 'Martes',
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
        'aula' => 'Aula 2',
        'estado' => 'activo',
    ]);
    $equipoAjeno = Equipo::create([
        'codigo' => 'EQ-REPORTE-AJENO',
        'tipo_equipo' => 'Laptop',
        'marca' => 'Marca',
        'modelo' => 'Modelo ajeno',
        'modo_uso' => 'individual',
        'estado' => 'En uso',
    ]);
    $asignacionAjena = Asignacion::create([
        'equipo_id' => $equipoAjeno->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $otraMateria->id,
        'horario_id' => $otroHorario->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-14 11:00:00',
        'estado' => 'activa',
    ]);
    Incidencia::create([
        'equipo_id' => $equipoAjeno->id,
        'asignacion_id' => $asignacionAjena->id,
        'tipo' => 'Hardware',
        'descripcion' => 'Incidencia ajena al reporte',
        'prioridad' => 'Alta',
        'estado' => 'Reportada',
        'fecha_reporte' => '2026-10-14 12:00:00',
    ]);

    $this->actingAs($context['teacher'])
        ->get(route('reportes.index', [
            'desde' => '2026-10-14',
            'hasta' => '2026-10-15',
        ]))
        ->assertSeeText('EQ-REPORTE-DOCENTE')
        ->assertDontSeeText('EQ-REPORTE-AJENO')
        ->assertSeeText('Incidencia propia del periodo')
        ->assertDontSeeText('Incidencia fuera del periodo')
        ->assertDontSeeText('Incidencia ajena al reporte')
        ->assertDontSeeText('13/10/2026')
        ->assertSeeText('Total 1');
});

test('report date filters reject an end date before the start date', function () {
    $context = createTeacherAssignmentContext();

    $this->actingAs($context['teacher'])
        ->from(route('reportes.index'))
        ->get(route('reportes.index', [
            'desde' => '2026-10-15',
            'hasta' => '2026-10-14',
        ]))
        ->assertSessionHasErrors('hasta');
});

test('docentes cannot assign a group equipment in maintenance', function () {
    $context = createTeacherAssignmentContext();
    $context['equipo']->update([
        'modo_uso' => 'grupal',
        'estado' => 'Mantenimiento',
    ]);

    $this->actingAs($context['teacher'])
        ->from(route('asignaciones.create'))
        ->post(route('asignaciones.store'), [
            'equipo_id' => $context['equipo']->id,
            'estudiante_id' => $context['estudiante']->id,
            'horario_id' => $context['horario']->id,
            'fecha_inicio' => '2026-10-14 09:00:00',
        ])
        ->assertRedirect(route('asignaciones.create'))
        ->assertSessionHasErrors([
            'equipo_id' => 'El equipo seleccionado no está disponible para una nueva asignación.',
        ]);

    $this->assertDatabaseMissing('asignaciones', [
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
    ]);
});

test('docentes can assign a group equipment that is already in use', function () {
    $context = createTeacherAssignmentContext();
    $context['equipo']->update([
        'modo_uso' => 'grupal',
        'estado' => 'En uso',
    ]);
    Asignacion::create([
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $context['materia']->id,
        'horario_id' => $context['horario']->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-14 08:00:00',
        'estado' => 'activa',
    ]);
    $otroEstudiante = Estudiante::create([
        'nombres' => 'Segundo Estudiante',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Sistema',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 'Primero',
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    DB::table('inscripciones')->insert([
        'estudiante_id' => $otroEstudiante->id,
        'materia_id' => $context['materia']->id,
        'gestion' => '2026-II',
        'tipo' => 'Normal',
        'estado' => 'Cursando',
        'fecha_inscripcion' => '2026-10-01',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($context['teacher'])
        ->post(route('asignaciones.store'), [
            'equipo_id' => $context['equipo']->id,
            'estudiante_id' => $otroEstudiante->id,
            'horario_id' => $context['horario']->id,
            'fecha_inicio' => '2026-10-14 09:00:00',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('asignaciones.index'));

    $this->assertDatabaseHas('asignaciones', [
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $otroEstudiante->id,
        'estado' => 'activa',
    ]);
});

test('docentes cannot assign equipment using another docente schedule', function () {
    $context = createTeacherAssignmentContext();
    $otroDocente = createAssignmentDocente();
    $materiaAjena = Materia::create([
        'nombre' => 'Materia ajena',
        'semestre' => 'Segundo',
        'estado' => 'activo',
    ]);
    $horarioAjeno = Horario::create([
        'docente_id' => $otroDocente->id,
        'materia_id' => $materiaAjena->id,
        'dia_semana' => 'Martes',
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
        'aula' => 'Aula 2',
        'estado' => 'activo',
    ]);

    DB::table('inscripciones')->insert([
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $materiaAjena->id,
        'gestion' => '2026-II',
        'tipo' => 'Normal',
        'estado' => 'Cursando',
        'fecha_inscripcion' => '2026-10-01',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($context['teacher'])->post(route('asignaciones.store'), [
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'horario_id' => $horarioAjeno->id,
        'fecha_inicio' => '2026-10-14 09:00:00',
    ]);

    $response->assertSessionHasErrors('horario_id');
    $this->assertDatabaseMissing('asignaciones', [
        'equipo_id' => $context['equipo']->id,
        'horario_id' => $horarioAjeno->id,
    ]);
});

test('docentes cannot assign equipment to a student without an active enrollment', function () {
    $context = createTeacherAssignmentContext();
    DB::table('inscripciones')
        ->where('estudiante_id', $context['estudiante']->id)
        ->where('materia_id', $context['materia']->id)
        ->update(['estado' => 'inactivo']);

    $this->actingAs($context['teacher'])
        ->post(route('asignaciones.store'), [
            'equipo_id' => $context['equipo']->id,
            'estudiante_id' => $context['estudiante']->id,
            'horario_id' => $context['horario']->id,
            'fecha_inicio' => '2026-10-14 09:00:00',
        ])
        ->assertSessionHasErrors([
            'estudiante_id' => 'El estudiante no tiene una inscripción activa en esta materia.',
        ]);

    $this->assertDatabaseMissing('asignaciones', [
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
    ]);
});

test('docentes can finish their active assignments after their schedule is deactivated', function () {
    $context = createTeacherAssignmentContext();
    $context['horario']->update(['estado' => 'inactivo']);
    $context['equipo']->update(['estado' => 'En uso']);
    $asignacion = Asignacion::create([
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $context['materia']->id,
        'horario_id' => $context['horario']->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-14 09:00:00',
        'estado' => 'activa',
    ]);

    $this->actingAs($context['teacher'])
        ->put(route('asignaciones.finalizar', $asignacion->id))
        ->assertRedirect(route('asignaciones.index'));

    $this->assertDatabaseHas('asignaciones', [
        'id' => $asignacion->id,
        'estado' => 'finalizada',
    ]);
    $this->assertDatabaseHas('equipos', [
        'id' => $context['equipo']->id,
        'estado' => 'Disponible',
    ]);
});

test('docentes can report and view incidents only for assignments in their own classes', function () {
    $context = createTeacherAssignmentContext();
    $asignacion = Asignacion::create([
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $context['materia']->id,
        'horario_id' => $context['horario']->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-14 09:00:00',
        'estado' => 'activa',
    ]);

    $this->actingAs($context['teacher'])
        ->post(route('incidencias.store'), [
            'asignacion_id' => $asignacion->id,
            'tipo' => 'Hardware',
            'descripcion' => 'El teclado no responde.',
            'prioridad' => 'Media',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('incidencias.index'));

    $incidencia = Incidencia::query()->firstOrFail();
    $this->assertSame($context['equipo']->id, $incidencia->equipo_id);
    $this->assertSame('Reportada', $incidencia->estado);

    $this->actingAs($context['teacher'])
        ->get(route('incidencias.index'))
        ->assertOk()
        ->assertSee('El teclado no responde.');

    $otroDocente = createAssignmentDocente();
    $otroUsuario = User::create([
        'name' => 'Otro docente',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'docente',
        'docente_id' => $otroDocente->id,
    ]);
    $materiaAjena = Materia::create([
        'nombre' => 'Materia de otro docente',
        'semestre' => 'Segundo',
        'estado' => 'activo',
    ]);
    $horarioAjeno = Horario::create([
        'docente_id' => $otroDocente->id,
        'materia_id' => $materiaAjena->id,
        'dia_semana' => 'Martes',
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
        'aula' => 'Aula 2',
        'estado' => 'activo',
    ]);
    $asignacionAjena = Asignacion::create([
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $materiaAjena->id,
        'horario_id' => $horarioAjeno->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-14 09:00:00',
        'estado' => 'activa',
    ]);

    $this->actingAs($context['teacher'])
        ->post(route('incidencias.store'), [
            'asignacion_id' => $asignacionAjena->id,
            'tipo' => 'Hardware',
            'descripcion' => 'Reporte no autorizado.',
            'prioridad' => 'Media',
        ])
        ->assertSessionHasErrors([
            'asignacion_id' => 'La asignación no está dentro de tu ámbito autorizado.',
        ]);
    $this->assertDatabaseMissing('incidencias', [
        'descripcion' => 'Reporte no autorizado.',
    ]);

    $this->actingAs($otroUsuario)
        ->get(route('incidencias.index'))
        ->assertOk()
        ->assertDontSee('El teclado no responde.');
});

test('docentes can filter incidents by status and priority within their own classes', function () {
    $context = createTeacherAssignmentContext();
    $asignacionActiva = Asignacion::create([
        'equipo_id' => $context['equipo']->id,
        'estudiante_id' => $context['estudiante']->id,
        'materia_id' => $context['materia']->id,
        'horario_id' => $context['horario']->id,
        'origen' => 'panel_web',
        'fecha_inicio' => '2026-10-14 09:00:00',
        'estado' => 'activa',
    ]);
    Incidencia::create([
        'equipo_id' => $context['equipo']->id,
        'asignacion_id' => $asignacionActiva->id,
        'tipo' => 'Hardware',
        'descripcion' => 'Falla de pantalla urgente',
        'prioridad' => 'Alta',
        'estado' => 'Reportada',
        'fecha_reporte' => '2026-10-15 09:00:00',
    ]);
    Incidencia::create([
        'equipo_id' => $context['equipo']->id,
        'asignacion_id' => $asignacionActiva->id,
        'tipo' => 'Software',
        'descripcion' => 'Error resuelto de aplicación',
        'prioridad' => 'Baja',
        'estado' => 'Resuelta',
        'fecha_reporte' => '2026-10-14 09:00:00',
        'fecha_resolucion' => '2026-10-14 10:00:00',
    ]);

    $this->actingAs($context['teacher'])
        ->get(route('incidencias.index', [
            'estado' => 'pendientes',
            'prioridad' => 'Alta',
            'buscar' => 'pantalla',
        ]))
        ->assertSeeText('Falla de pantalla urgente')
        ->assertDontSeeText('Error resuelto de aplicación');

    $this->actingAs($context['teacher'])
        ->get(route('incidencias.index', ['estado' => 'resueltas']))
        ->assertSeeText('Error resuelto de aplicación')
        ->assertDontSeeText('Falla de pantalla urgente');
});

test('administrators cannot register an incident as already resolved', function () {
    $equipment = Equipo::create([
        'codigo' => 'EQ-INC-001',
        'tipo_equipo' => 'Laptop',
        'marca' => 'Marca',
        'modelo' => 'Modelo de prueba',
        'modo_uso' => 'individual',
        'estado' => 'Disponible',
    ]);
    $adminDocente = createAssignmentDocente();
    $admin = User::create([
        'name' => 'Administrador de incidencias',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'administrador',
        'docente_id' => $adminDocente->id,
    ]);

    $this->actingAs($admin)
        ->from(route('incidencias.create'))
        ->post(route('incidencias.store'), [
            'equipo_id' => $equipment->id,
            'tipo' => 'Hardware',
            'descripcion' => 'No debe poder registrarse como resuelta.',
            'prioridad' => 'Alta',
            'estado' => 'Resuelta',
            'fecha_reporte' => '2026-10-15 09:00:00',
        ])
        ->assertRedirect(route('incidencias.create'))
        ->assertSessionHasErrors('estado');

    $this->assertDatabaseMissing('incidencias', [
        'equipo_id' => $equipment->id,
        'descripcion' => 'No debe poder registrarse como resuelta.',
    ]);
});

/**
 * @return array{
 *     teacher: User,
 *     materia: Materia,
 *     horario: Horario,
 *     estudiante: Estudiante,
 *     equipo: Equipo
 * }
 */
function createTeacherAssignmentContext(): array
{
    $docente = createAssignmentDocente();
    $teacher = User::create([
        'name' => 'Docente de prueba',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'docente',
        'docente_id' => $docente->id,
    ]);
    $materia = Materia::create([
        'nombre' => 'Materia propia',
        'semestre' => 'Primero',
        'estado' => 'activo',
    ]);
    $horario = Horario::create([
        'docente_id' => $docente->id,
        'materia_id' => $materia->id,
        'dia_semana' => 'Lunes',
        'hora_inicio' => '08:00:00',
        'hora_fin' => '10:00:00',
        'aula' => 'Aula 1',
        'estado' => 'activo',
    ]);
    $estudiante = Estudiante::create([
        'nombres' => 'Estudiante',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Sistema',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 'Primero',
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $equipo = Equipo::create([
        'codigo' => fake()->unique()->bothify('EQ-####'),
        'tipo_equipo' => 'Laptop',
        'marca' => 'Marca',
        'modelo' => 'Modelo de prueba',
        'modo_uso' => 'individual',
        'estado' => 'Disponible',
    ]);

    DB::table('inscripciones')->insert([
        'estudiante_id' => $estudiante->id,
        'materia_id' => $materia->id,
        'gestion' => '2026-II',
        'tipo' => 'Normal',
        'estado' => 'Cursando',
        'fecha_inscripcion' => '2026-10-01',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return compact('teacher', 'materia', 'horario', 'estudiante', 'equipo');
}

function createAssignmentDocente(): Docente
{
    return Docente::create([
        'nombres' => 'Docente',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Asignación',
        'ci' => fake()->unique()->numerify('########'),
        'cargo' => 'Docente',
        'estado' => 'activo',
    ]);
}

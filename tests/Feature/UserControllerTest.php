<?php

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('administrators can download a user profile PDF template', function () {
    $admin = createUserAdminActor();
    $docente = Docente::create([
        'nombres' => 'Docente',
        'apellido_paterno' => 'Plantilla',
        'apellido_materno' => 'Prueba',
        'ci' => '12345678',
        'ci_extension' => 'LP',
        'cargo' => 'Docente',
        'estado' => 'activo',
    ]);
    $usuario = User::create([
        'username' => 'docente-plantilla',
        'password' => Hash::make('password-segura'),
        'tipo_usuario' => 'administrador',
        'docente_id' => $docente->id,
        'activo' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('usuarios.export', $usuario->id));

    $response->assertOk()->assertHeader('content-type', 'application/pdf');
    expect($response->headers->get('content-disposition'))
        ->toContain('usuario-docente-plantilla-plantilla.pdf');
});

test('administrators can create a docente profile and account together', function () {
    $admin = createUserAdminActor();

    $response = $this->actingAs($admin)->post(route('usuarios.store'), [
        'username' => 'docente-prueba',
        'tipo_usuario' => 'docente',
        'password' => 'password-segura',
        'password_confirmation' => 'password-segura',
        'perfil' => [
            'nombres' => 'Ana',
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => 'Docente',
            'ci' => '12345678',
            'ci_extension' => 'LP',
            'telefono' => '70000000',
            'cargo' => 'Docente',
            'nivel_academico' => 'Licenciatura',
            'fecha_ingreso' => '2026-01-10',
        ],
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('usuarios.index', ['categoria' => 'docente']));

    $user = User::where('username', 'docente-prueba')->firstOrFail();
    $this->assertDatabaseHas('docentes', [
        'id' => $user->docente_id,
        'nombres' => 'Ana',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Docente',
        'ci' => '12345678',
        'ci_extension' => 'LP',
        'estado' => 'activo',
    ]);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Ana Prueba Docente',
        'username' => 'docente-prueba',
        'tipo_usuario' => 'docente',
        'docente_id' => $user->docente_id,
        'estudiante_id' => null,
    ]);
    expect(Hash::check('password-segura', $user->password))->toBeTrue();
});

test('administrators can create a second administrator with a new docente profile', function () {
    $admin = createUserAdminActor();

    $response = $this->actingAs($admin)->post(route('usuarios.store'), [
        'username' => 'admin-secundario',
        'tipo_usuario' => 'administrador',
        'password' => 'password-segura',
        'password_confirmation' => 'password-segura',
        'perfil' => [
            'nombres' => 'Mario',
            'apellido_paterno' => 'Admin',
            'apellido_materno' => 'Nuevo',
            'ci' => '12567890',
            'ci_extension' => 'SC',
            'cargo' => 'Administrador',
        ],
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('usuarios.index', ['categoria' => 'administrador']));

    $user = User::where('username', 'admin-secundario')->firstOrFail();
    $this->assertDatabaseHas('docentes', [
        'id' => $user->docente_id,
        'nombres' => 'Mario',
        'cargo' => 'Administrador',
        'estado' => 'activo',
    ]);
    expect($user->tipo_usuario)->toBe('administrador');
});

test('administrators can create a student profile and account together', function () {
    Storage::fake('public');
    $admin = createUserAdminActor();

    $response = $this->actingAs($admin)->post(route('usuarios.store'), [
        'username' => 'estudiante-prueba',
        'tipo_usuario' => 'estudiante',
        'password' => 'password-segura',
        'password_confirmation' => 'password-segura',
        'foto_personal' => UploadedFile::fake()->image('estudiante.jpg'),
        'perfil' => [
            'nombres' => 'Luis',
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => 'Estudiante',
            'ci' => '87654321',
            'ci_extension' => 'CB',
            'telefono' => '71111111',
            'matricula' => 'MAT1001',
            'semestre' => 1,
            'generacion_ingreso' => 'I-2027',
            'correo_institucional' => 'luis@instituto.edu',
            'correo_personal' => 'luis@example.test',
        ],
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('usuarios.index', ['categoria' => 'estudiante']));

    $user = User::where('username', 'estudiante-prueba')->firstOrFail();
    $this->assertDatabaseHas('estudiantes', [
        'id' => $user->estudiante_id,
        'nombres' => 'Luis',
        'matricula' => 'MAT1001',
        'semestre' => 1,
        'generacion_ingreso' => 'I-2027',
        'ci_extension' => 'CB',
        'estado' => 'activo',
    ]);
    $student = Estudiante::findOrFail($user->estudiante_id);
    expect($student->foto_personal)->toStartWith('fotos/estudiantes/');
    Storage::disk('public')->assertExists($student->foto_personal);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Luis Prueba Estudiante',
        'tipo_usuario' => 'estudiante',
        'docente_id' => null,
        'estudiante_id' => $user->estudiante_id,
    ]);
});

test('student accounts use numeric semesters in the creation form and request', function () {
    $admin = createUserAdminActor();

    $this->actingAs($admin)
        ->get(route('usuarios.create'))
        ->assertOk()
        ->assertSee('value="1"', false)
        ->assertSee('>Primero</option>', false)
        ->assertSee('value="4"', false)
        ->assertSee('>Cuarto</option>', false)
        ->assertSee('name="perfil[ci_extension]"', false)
        ->assertSee('value="LP"', false)
        ->assertSee('value="OR"', false)
        ->assertSee('value="PT"', false)
        ->assertSee('value="CB"', false)
        ->assertSee('value="SC"', false)
        ->assertSee('value="TJ"', false)
        ->assertSee('value="CH"', false)
        ->assertSee('value="BE"', false)
        ->assertSee('value="PD"', false)
        ->assertSee('Sugerir credenciales')
        ->assertSee('name="perfil[direccion]"', false);

    $response = $this->actingAs($admin)->post(route('usuarios.store'), [
        'username' => 'estudiante-cuarto',
        'tipo_usuario' => 'estudiante',
        'password' => 'password-segura',
        'password_confirmation' => 'password-segura',
        'perfil' => [
            'nombres' => 'Daniel',
            'apellido_paterno' => 'Cuarto',
            'apellido_materno' => 'Prueba',
            'ci' => '12345679',
            'ci_extension' => 'LP',
            'matricula' => 'MAT1002',
            'semestre' => 4,
            'generacion_ingreso' => 'II-2027',
        ],
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('usuarios.index', ['categoria' => 'estudiante']));

    $this->assertDatabaseHas('estudiantes', [
        'matricula' => 'MAT1002',
        'semestre' => 4,
        'generacion_ingreso' => 'II-2027',
    ]);
});

test('administrators cannot create an account without the required personal profile data', function () {
    $admin = createUserAdminActor();
    $docentesAntes = Docente::count();

    $response = $this->actingAs($admin)->post(route('usuarios.store'), [
        'username' => 'admin-sin-perfil',
        'tipo_usuario' => 'administrador',
        'password' => 'password-segura',
        'password_confirmation' => 'password-segura',
    ]);

    $response->assertSessionHasErrors([
        'perfil.nombres',
        'perfil.apellido_paterno',
        'perfil.apellido_materno',
        'perfil.ci',
    ]);
    $this->assertDatabaseMissing('users', ['username' => 'admin-sin-perfil']);
    $this->assertDatabaseCount('docentes', $docentesAntes);
});

test('administrators can edit personal data without changing a user account', function () {
    $admin = createUserAdminActor();
    $targetTeacher = createUserTargetTeacher();
    $targetTeacher->update([
        'correo_personal' => 'docente-original@example.test',
        'especialidad' => 'Sistemas informáticos',
        'fecha_ingreso' => '2020-02-03',
    ]);
    $targetUser = createUserTeacherAccount($targetTeacher);
    $originalUsername = $targetUser->username;
    $originalPassword = $targetUser->password;

    $this->actingAs($admin)
        ->get(route('usuarios.personal.edit', $targetUser->id))
        ->assertSee('Editar datos personales')
        ->assertSee($targetUser->username)
        ->assertSee('Correo personal')
        ->assertSee('Especialidad')
        ->assertSee('value="2020-02-03"', false)
        ->assertDontSee('name="password"')
        ->assertDontSee('name="username"');

    $response = $this->actingAs($admin)->patch(
        route('usuarios.personal.update', $targetUser->id),
        [
            'nombres' => 'Nombre editado',
            'apellido_paterno' => 'Apellido nuevo',
            'apellido_materno' => 'Segundo apellido nuevo',
            'ci' => $targetTeacher->ci,
            'ci_extension' => 'OR',
            'telefono' => '72222222',
            'correo_personal' => 'docente-actualizado@example.test',
            'cargo' => 'Docente actualizado',
            'especialidad' => 'Redes y telecomunicaciones',
            'nivel_academico' => 'Maestría',
            'fecha_ingreso' => '2021-04-05',
            'username' => 'usuario-no-permitido',
            'name' => 'Nombre de cuenta no permitido',
            'password' => 'nueva-password',
            'tipo_usuario' => 'administrador',
            'docente_id' => $admin->docente_id,
            'estado' => 'inactivo',
        ]
    );

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('usuarios.show', $targetUser->id));

    $this->assertDatabaseHas('docentes', [
        'id' => $targetTeacher->id,
        'nombres' => 'Nombre editado',
        'apellido_paterno' => 'Apellido nuevo',
        'apellido_materno' => 'Segundo apellido nuevo',
        'ci_extension' => 'OR',
        'telefono' => '72222222',
        'correo_personal' => 'docente-actualizado@example.test',
        'cargo' => 'Docente actualizado',
        'especialidad' => 'Redes y telecomunicaciones',
        'nivel_academico' => 'Maestría',
        'fecha_ingreso' => '2021-04-05',
        'estado' => 'activo',
    ]);

    $targetUser->refresh();
    expect($targetUser->username)->toBe($originalUsername);
    expect($targetUser->password)->toBe($originalPassword);
    expect($targetUser->tipo_usuario)->toBe('docente');
    expect($targetUser->docente_id)->toBe($targetTeacher->id);
});

test('administrators can edit student personal data without changing their account', function () {
    Storage::fake('public');
    $admin = createUserAdminActor();
    $student = Estudiante::create([
        'nombres' => 'Estudiante',
        'apellido_paterno' => 'Original',
        'apellido_materno' => 'Prueba',
        'matricula' => 'MAT2001',
        'semestre' => 1,
        'generacion_ingreso' => 'I-2024',
        'ci' => '99001122',
        'telefono' => '73333333',
        'correo_institucional' => 'original@instituto.edu',
        'estado' => 'activo',
    ]);
    $studentUser = User::create([
        'name' => 'Estudiante Original Prueba',
        'username' => 'estudiante-original',
        'password' => 'password-segura',
        'tipo_usuario' => 'estudiante',
        'estudiante_id' => $student->id,
    ]);

    $this->actingAs($admin)
        ->get(route('usuarios.personal.edit', $studentUser->id))
        ->assertOk()
        ->assertSee('value="2024"', false);

    $this->actingAs($admin)->patch(
        route('usuarios.personal.update', $studentUser->id),
        [
            'nombres' => 'Estudiante actualizado',
            'apellido_paterno' => 'Apellido actualizado',
            'apellido_materno' => 'Segundo actualizado',
            'matricula' => 'MAT2002',
            'semestre' => 2,
            'generacion_ingreso' => 'II-2025',
            'ci' => $student->ci,
            'ci_extension' => 'TJ',
            'telefono' => '74444444',
            'foto_personal' => UploadedFile::fake()->image('estudiante-actualizado.jpg'),
            'correo_institucional' => 'nuevo@instituto.edu',
            'correo_personal' => 'nuevo@example.test',
            'direccion' => 'Av. Principal 123',
            'username' => 'no-cambiar-usuario',
            'tipo_usuario' => 'administrador',
            'estado' => 'inactivo',
        ]
    )
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('usuarios.show', $studentUser->id));

    $this->assertDatabaseHas('estudiantes', [
        'id' => $student->id,
        'nombres' => 'Estudiante actualizado',
        'apellido_paterno' => 'Apellido actualizado',
        'apellido_materno' => 'Segundo actualizado',
        'matricula' => 'MAT2002',
        'semestre' => 2,
        'generacion_ingreso' => 'II-2025',
        'ci_extension' => 'TJ',
        'telefono' => '74444444',
        'correo_institucional' => 'nuevo@instituto.edu',
        'direccion' => 'Av. Principal 123',
        'estado' => 'activo',
    ]);
    $updatedStudent = $student->fresh();
    expect($updatedStudent->foto_personal)->toStartWith('fotos/estudiantes/');
    Storage::disk('public')->assertExists($updatedStudent->foto_personal);

    $this->actingAs($admin)
        ->get(route('usuarios.show', $studentUser->id))
        ->assertSee(Storage::disk('public')->url($updatedStudent->foto_personal), false);

    expect($studentUser->fresh()->username)->toBe('estudiante-original');
    expect($studentUser->fresh()->tipo_usuario)->toBe('estudiante');
});

test('administrators can update student semesters with numeric values', function () {
    $admin = createUserAdminActor();
    $student = Estudiante::create([
        'nombres' => 'Estudiante',
        'apellido_paterno' => 'Semestre',
        'apellido_materno' => 'Prueba',
        'matricula' => 'MAT2003',
        'semestre' => 3,
        'generacion_ingreso' => 'II-2026',
        'ci' => '99001125',
        'ci_extension' => 'LP',
        'estado' => 'activo',
    ]);
    $studentUser = User::create([
        'name' => 'Estudiante Semestre Prueba',
        'username' => 'estudiante-semestre',
        'password' => 'password-segura',
        'tipo_usuario' => 'estudiante',
        'estudiante_id' => $student->id,
    ]);

    $this->actingAs($admin)
        ->get(route('usuarios.personal.edit', $studentUser->id))
        ->assertOk()
        ->assertSee('value="3"', false)
        ->assertSee('value="4"', false)
        ->assertSee('>Cuarto</option>', false);

    $response = $this->actingAs($admin)->patch(
        route('usuarios.personal.update', $studentUser->id),
        [
            'nombres' => 'Estudiante',
            'apellido_paterno' => 'Semestre',
            'apellido_materno' => 'Prueba',
            'matricula' => 'MAT2003',
            'semestre' => 4,
            'generacion_ingreso' => 'II-2026',
            'ci' => '99001125',
            'ci_extension' => 'LP',
        ]
    );

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('usuarios.show', $studentUser->id));

    $this->assertDatabaseHas('estudiantes', [
        'id' => $student->id,
        'semestre' => 4,
    ]);
});

test('administrators can record the end of system tracking after all third-semester enrollments are approved', function () {
    $admin = createUserAdminActor();
    $student = Estudiante::create([
        'nombres' => 'Estudiante',
        'apellido_paterno' => 'Tercero',
        'apellido_materno' => 'Prueba',
        'matricula' => 'MAT3001',
        'semestre' => 3,
        'generacion_ingreso' => 'I-2026',
        'ci' => '99001123',
        'estado' => 'activo',
    ]);
    $studentUser = User::create([
        'name' => 'Estudiante Tercero Prueba',
        'username' => 'estudiante-tercero',
        'password' => 'password-segura',
        'tipo_usuario' => 'estudiante',
        'estudiante_id' => $student->id,
    ]);
    $subject = Materia::create([
        'nombre' => 'Materia de Tercero',
        'semestre' => 3,
        'estado' => 'activo',
    ]);
    $enrollment = Inscripcion::create([
        'estudiante_id' => $student->id,
        'materia_id' => $subject->id,
        'gestion' => '2027-I',
        'tipo' => 'Normal',
        'estado' => 'Aprobado',
        'fecha_inscripcion' => '2027-02-01',
    ]);

    $this->actingAs($admin)
        ->patch(route('usuarios.seguimiento.finalizar', $studentUser->id), [
            'gestion_fin' => '2027-I',
        ])
        ->assertRedirect(route('usuarios.show', $studentUser->id))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('historial_seguimiento_estudiantes', [
        'estudiante_id' => $student->id,
        'gestion_fin' => '2027-I',
        'registrado_por' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->from(route('usuarios.show', $studentUser->id))
        ->patch(route('inscripciones.status', $enrollment), [
            'estado' => 'Inactivo',
        ])
        ->assertSessionHasErrors('estado');

    $this->actingAs($admin)
        ->get(route('usuarios.show', $studentUser->id))
        ->assertOk()
        ->assertSeeText('Generación de ingreso')
        ->assertSeeText('I-2026')
        ->assertDontSeeText('Historial de seguimiento en este sistema')
        ->assertDontSeeText('Gestión de finalización')
        ->assertDontSeeText('Registrado por');

    $nextSubject = Materia::create([
        'nombre' => 'Otra materia de Tercero',
        'semestre' => 3,
        'estado' => 'activo',
    ]);

    $this->actingAs($admin)
        ->from(route('inscripciones.create'))
        ->post(route('inscripciones.store'), [
            'estudiante_id' => $student->id,
            'materia_id' => $nextSubject->id,
            'gestion' => '2027-II',
            'tipo' => 'Arrastre',
            'fecha_inscripcion' => '2027-08-01',
        ])
        ->assertSessionHasErrors('estudiante_id');
});

test('administrators cannot end system tracking before third-semester enrollments are approved', function () {
    $admin = createUserAdminActor();
    $student = Estudiante::create([
        'nombres' => 'Estudiante',
        'apellido_paterno' => 'Tercero',
        'apellido_materno' => 'Pendiente',
        'matricula' => 'MAT3002',
        'semestre' => 3,
        'generacion_ingreso' => 'II-2026',
        'ci' => '99001124',
        'estado' => 'activo',
    ]);
    $studentUser = User::create([
        'name' => 'Estudiante Tercero Pendiente',
        'username' => 'estudiante-tercero-pendiente',
        'password' => 'password-segura',
        'tipo_usuario' => 'estudiante',
        'estudiante_id' => $student->id,
    ]);
    $subject = Materia::create([
        'nombre' => 'Materia pendiente',
        'semestre' => 3,
        'estado' => 'activo',
    ]);
    Inscripcion::create([
        'estudiante_id' => $student->id,
        'materia_id' => $subject->id,
        'gestion' => '2027-I',
        'tipo' => 'Normal',
        'estado' => 'Cursando',
        'fecha_inscripcion' => '2027-02-01',
    ]);

    $this->actingAs($admin)
        ->from(route('usuarios.show', $studentUser->id))
        ->patch(route('usuarios.seguimiento.finalizar', $studentUser->id), [
            'gestion_fin' => '2027-I',
        ])
        ->assertSessionHasErrors([
            'gestion_fin' => 'Todas las inscripciones de Tercero de la gestión deben estar aprobadas.',
        ]);

    $this->assertDatabaseMissing('historial_seguimiento_estudiantes', [
        'estudiante_id' => $student->id,
    ]);
});

test('docentes cannot edit personal data from administrator user management', function () {
    $teacher = createUserTeacherAccount(createUserTargetTeacher());
    $otherTeacher = createUserTeacherAccount(createUserTargetTeacher());

    $this->actingAs($teacher)
        ->get(route('usuarios.personal.edit', $otherTeacher->id))
        ->assertForbidden();

    $this->assertDatabaseHas('docentes', [
        'id' => $otherTeacher->docente_id,
        'nombres' => 'Docente',
    ]);
});

test('administrators can activate or deactivate accounts through profile state', function () {
    $admin = createUserAdminActor();
    $targetTeacher = createUserTargetTeacher('activo');
    $targetUser = createUserTeacherAccount($targetTeacher);

    $response = $this->actingAs($admin)->patch(
        route('usuarios.status', $targetUser->id),
        ['estado' => 'inactivo']
    );

    $response->assertRedirect(route('usuarios.show', $targetUser->id));
    $this->assertDatabaseHas('docentes', [
        'id' => $targetTeacher->id,
        'estado' => 'inactivo',
    ]);
});

test('administrators can archive a user and its personal profile', function () {
    $admin = createUserAdminActor();
    $targetTeacher = createUserTargetTeacher();
    $targetUser = createUserTeacherAccount($targetTeacher);

    $response = $this->actingAs($admin)->delete(route('usuarios.destroy', $targetUser->id));

    $response
        ->assertRedirect(route('usuarios.index', ['categoria' => 'docente']))
        ->assertSessionHas('success');

    $this->assertSoftDeleted('users', ['id' => $targetUser->id]);
    $this->assertSoftDeleted('docentes', ['id' => $targetTeacher->id]);
    $this->assertDatabaseMissing('users', ['id' => $targetUser->id, 'deleted_at' => null]);
    $this->assertDatabaseMissing('docentes', ['id' => $targetTeacher->id, 'deleted_at' => null]);
});

test('administrators cannot archive their own account', function () {
    $admin = createUserAdminActor();

    $this->actingAs($admin)
        ->delete(route('usuarios.destroy', $admin->id))
        ->assertForbidden();

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'deleted_at' => null,
    ]);
});

test('administrators can view archived profiles', function () {
    $admin = createUserAdminActor();
    $targetTeacher = createUserTargetTeacher();
    $targetUser = createUserTeacherAccount($targetTeacher);

    $this->actingAs($admin)->delete(route('usuarios.destroy', $targetUser->id));

    $this->actingAs($admin)
        ->get(route('usuarios.deleted'))
        ->assertOk()
        ->assertSee($targetUser->username)
        ->assertSee('Perfiles eliminados');
});

test('administrators can recover docente accounts with temporary credentials', function () {
    $admin = createUserAdminActor();
    $targetUser = createUserTeacherAccount(createUserTargetTeacher());
    $targetUser->docente->update([
        'nombres' => 'Miguel Angel',
        'ci' => '15645072',
    ]);

    $response = $this->actingAs($admin)->postJson(
        route('usuarios.recover', $targetUser->id),
        ['admin_password' => 'password']
    );

    $response->assertOk()->assertJsonPath('success', true);

    $username = $response->json('username');
    $password = $response->json('password');
    $updatedUser = $targetUser->fresh();

    expect($username)->toBe('miguelangel_prueba');
    expect($password)->toBe('prueba15645072');
    expect($updatedUser->username)->toBe($username);
    expect($updatedUser->debe_cambiar_password)->toBeTrue();
    expect(Hash::check($password, $updatedUser->password))->toBeTrue();
});

test('administrators cannot recover docente accounts with an incorrect password', function () {
    $admin = createUserAdminActor();
    $targetUser = createUserTeacherAccount(createUserTargetTeacher());
    $originalUsername = $targetUser->username;
    $originalPassword = $targetUser->password;

    $response = $this->actingAs($admin)->postJson(
        route('usuarios.recover', $targetUser->id),
        ['admin_password' => 'incorrecta']
    );

    $response->assertUnprocessable()->assertJsonPath('success', false);
    expect($targetUser->fresh()->username)->toBe($originalUsername);
    expect($targetUser->fresh()->password)->toBe($originalPassword);
});

test('administrators can recover estudiante accounts with temporary credentials', function () {
    $admin = createUserAdminActor();
    $student = Estudiante::create([
        'nombres' => 'Estudiante',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Sistema',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 1,
        'generacion_ingreso' => 'I-2026',
        'ci' => fake()->unique()->numerify('########'),
        'ci_extension' => 'LP',
        'estado' => 'activo',
    ]);
    $targetUser = User::create([
        'name' => 'Estudiante',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'estudiante',
        'estudiante_id' => $student->id,
    ]);

    $response = $this->actingAs($admin)->postJson(
        route('usuarios.recover', $targetUser->id),
        ['admin_password' => 'password']
    );

    $response->assertOk()->assertJsonPath('success', true);
    expect($response->json('username'))->toBe('estudiante_prueba');
    expect($response->json('password'))
        ->toBe('prueba'.$student->ci);
    expect($targetUser->fresh()->debe_cambiar_password)->toBeTrue();
});

test('administrators cannot deactivate their own account', function () {
    $admin = createUserAdminActor();

    $response = $this->actingAs($admin)->patch(
        route('usuarios.status', $admin->id),
        ['estado' => 'inactivo']
    );

    $response
        ->assertRedirect()
        ->assertSessionHas('error', 'No puedes desactivar tu propia cuenta.');

    $this->assertDatabaseHas('docentes', [
        'id' => $admin->docente_id,
        'estado' => 'activo',
    ]);
});

test('administrators can activate or deactivate a second administrator account', function () {
    $admin = createUserAdminActor();
    $secondAdminProfile = createUserTargetTeacher('Activa');
    $secondAdmin = User::create([
        'name' => 'Segundo administrador',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'administrador',
        'docente_id' => $secondAdminProfile->id,
    ]);

    $this->actingAs($admin)
        ->get(route('usuarios.show', $secondAdmin->id))
        ->assertDontSee(route('usuarios.personal.edit', $secondAdmin->id))
        ->assertSee('Desactivar cuenta');

    $this->actingAs($admin)
        ->patch(route('usuarios.status', $secondAdmin->id), ['estado' => 'inactivo'])
        ->assertRedirect(route('usuarios.show', $secondAdmin->id));

    $this->assertDatabaseHas('docentes', [
        'id' => $secondAdminProfile->id,
        'estado' => 'inactivo',
    ]);

    $this->actingAs($admin)
        ->patch(route('usuarios.status', $secondAdmin->id), ['estado' => 'activo'])
        ->assertRedirect(route('usuarios.show', $secondAdmin->id));

    $this->assertDatabaseHas('docentes', [
        'id' => $secondAdminProfile->id,
        'estado' => 'activo',
    ]);
});

test('administrators cannot edit account data through the user routes', function () {
    $admin = createUserAdminActor();
    $targetUser = createUserTeacherAccount(createUserTargetTeacher());

    $this->actingAs($admin)
        ->get('/usuarios/'.$targetUser->id.'/editar')
        ->assertNotFound();

    $this->actingAs($admin)
        ->put('/usuarios/'.$targetUser->id, [
            'name' => 'Nombre no permitido',
        ])
        ->assertStatus(405);

    $this->actingAs($admin)
        ->delete('/usuarios/'.$targetUser->id)
        ->assertStatus(405);

    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'name' => $targetUser->name,
    ]);
});

test('administrators can browse and search people by category', function () {
    $admin = createUserAdminActor();
    $teacher = createUserTargetTeacher();
    $teacherUser = createUserTeacherAccount($teacher);

    $this->actingAs($admin)
        ->get(route('usuarios.index', ['categoria' => 'docente']))
        ->assertOk()
        ->assertSee('Personas y usuarios')
        ->assertSee('Docentes')
        ->assertSee($teacherUser->username)
        ->assertSee('Docente Prueba Sistema');

    $this->actingAs($admin)
        ->get(route('usuarios.index', [
            'categoria' => 'administrador',
            'buscar' => $admin->username,
        ]))
        ->assertOk()
        ->assertSee($admin->username);
});

test('administrators can search students by names surnames username or matricula without case sensitivity', function () {
    $admin = createUserAdminActor();
    $student = Estudiante::create([
        'nombres' => 'Cris',
        'apellido_paterno' => 'Mendoza',
        'apellido_materno' => 'Vega',
        'matricula' => 'VRFT1234',
        'semestre' => 1,
        'generacion_ingreso' => 'I-2026',
        'ci' => fake()->unique()->numerify('########'),
        'ci_extension' => 'LP',
        'estado' => 'activo',
    ]);
    $studentUser = User::create([
        'name' => 'Cris Mendoza Vega',
        'username' => 'cris_mendo',
        'password' => 'password',
        'tipo_usuario' => 'estudiante',
        'estudiante_id' => $student->id,
    ]);

    foreach (['cris', 'MENDOZA', 'vega', 'CRIS_MENDO', 'vrft1234'] as $term) {
        $this->actingAs($admin)
            ->get(route('usuarios.index', ['categoria' => 'estudiante', 'buscar' => $term]))
            ->assertOk()
            ->assertSee($studentUser->username);
    }
});

test('administrators can search students across all semesters', function () {
    $admin = createUserAdminActor();
    $firstStudent = Estudiante::create([
        'nombres' => 'Vega Primero',
        'apellido_paterno' => 'Rojas',
        'apellido_materno' => 'Quispe',
        'matricula' => 'VRFT2001',
        'semestre' => 1,
        'generacion_ingreso' => 'I-2026',
        'ci' => fake()->unique()->numerify('########'),
        'ci_extension' => 'LP',
        'estado' => 'activo',
    ]);
    $secondStudent = Estudiante::create([
        'nombres' => 'Vega Cuarto',
        'apellido_paterno' => 'Rojas',
        'apellido_materno' => 'Mamani',
        'matricula' => 'VRFT2004',
        'semestre' => 4,
        'generacion_ingreso' => 'II-2026',
        'ci' => fake()->unique()->numerify('########'),
        'ci_extension' => 'LP',
        'estado' => 'activo',
    ]);
    $firstUser = User::create([
        'name' => 'Vega Primero Rojas Quispe',
        'username' => 'vega_primero',
        'password' => 'password',
        'tipo_usuario' => 'estudiante',
        'estudiante_id' => $firstStudent->id,
    ]);
    $secondUser = User::create([
        'name' => 'Vega Cuarto Rojas Mamani',
        'username' => 'vega_cuarto',
        'password' => 'password',
        'tipo_usuario' => 'estudiante',
        'estudiante_id' => $secondStudent->id,
    ]);

    $this->actingAs($admin)
        ->get(route('usuarios.index', [
            'categoria' => 'estudiante',
            'semestre' => 'todos',
            'buscar' => 'ROJAS',
        ]))
        ->assertOk()
        ->assertSee($firstUser->username)
        ->assertSee($secondUser->username);
});

function createUserAdminActor(): User
{
    $docente = createUserTargetTeacher();

    return User::create([
        'name' => 'Administrador',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'administrador',
        'docente_id' => $docente->id,
    ]);
}

function createUserTargetTeacher(string $estado = 'activo'): Docente
{
    return Docente::create([
        'nombres' => 'Docente',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Sistema',
        'ci' => fake()->unique()->numerify('########'),
        'cargo' => 'Docente',
        'estado' => $estado,
    ]);
}

function createUserTeacherAccount(Docente $docente): User
{
    return User::create([
        'name' => 'Docente',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'docente',
        'docente_id' => $docente->id,
    ]);
}

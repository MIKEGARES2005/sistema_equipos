<?php

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('a pure administrator can open their profile without a personal profile', function () {
    $admin = User::factory()->administrador()->create();

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Perfil del administrador')
        ->assertSee('no tiene un perfil personal asociado')
        ->assertSee('name="username"', false)
        ->assertSee('id="rol_display"', false)
        ->assertDontSee('name="tipo_usuario"', false)
        ->assertSee('name="current_password"', false);
});

test('a pure administrator can update their username without changing their role', function () {
    $admin = User::factory()->administrador()->create([
        'username' => 'admin-anterior',
    ]);

    $this->actingAs($admin)
        ->patch(route('profile.update'), [
            'username' => 'admin-nuevo',
            'tipo_usuario' => 'docente',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $admin->refresh();

    expect($admin->username)->toBe('admin-nuevo')
        ->and($admin->username_cambiado_en)->not->toBeNull()
        ->and($admin->tipo_usuario)->toBe('administrador');
});

test('profile username must be unique across accounts', function () {
    $admin = User::factory()->administrador()->create([
        'username' => 'admin-uno',
    ]);
    $otroAdmin = User::factory()->administrador()->create([
        'username' => 'admin-dos',
    ]);

    $this->actingAs($admin)
        ->from(route('profile.edit'))
        ->patch(route('profile.update'), [
            'username' => $otroAdmin->username,
        ])
        ->assertSessionHasErrors('username')
        ->assertRedirect(route('profile.edit'));

    expect($admin->fresh()->username)->toBe('admin-uno');
});

test('an existing username containing a dot can be saved unchanged', function () {
    $admin = User::factory()->administrador()->create([
        'username' => 'admin.demo',
    ]);

    $this->actingAs($admin)
        ->patch(route('profile.update'), [
            'username' => 'admin.demo',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($admin->fresh()->username)->toBe('admin.demo');
});

test('a pure administrator can open the staff dashboard', function () {
    $admin = User::factory()->administrador()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk();
});

test('a student can update their contact data and username', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Ana',
        'apellido_paterno' => 'Quispe',
        'apellido_materno' => 'Mamani',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 3,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $user = User::factory()->estudiante($estudiante)->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Datos académicos')
        ->assertSee($estudiante->matricula)
        ->assertSee('Datos de contacto')
        ->assertDontSee('Datos personales');

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'username' => 'ana_quispe_nueva',
            'telefono' => '71111111',
            'correo_personal' => 'ana.nueva@example.com',
            'direccion' => 'Av. Nueva #456',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $estudiante->refresh();
    $user->refresh();

    expect($estudiante->telefono)->toBe('71111111')
        ->and($estudiante->correo_personal)->toBe('ana.nueva@example.com')
        ->and($estudiante->direccion)->toBe('Av. Nueva #456')
        ->and($estudiante->nombres)->toBe('Ana')
        ->and($user->username)->toBe('ana_quispe_nueva')
        ->and($user->tipo_usuario)->toBe('estudiante');
});

test('a student profile photo is served through the student portal route', function () {
    Storage::fake('public');
    $estudiante = Estudiante::create([
        'nombres' => 'Foto',
        'apellido_paterno' => 'Portal',
        'apellido_materno' => 'Prueba',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 1,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
        'foto_personal' => 'fotos/estudiantes/perfil.jpg',
    ]);
    Storage::disk('public')->put($estudiante->foto_personal, 'imagen-de-prueba');
    $user = User::factory()->estudiante($estudiante)->create();

    $this->actingAs($user, 'student')
        ->get(route('estudiante.profile.photo'))
        ->assertOk();
});

test('a student can update profile data and password with one submission', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Luis',
        'apellido_paterno' => 'Mendoza',
        'apellido_materno' => 'Vega',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 1,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $user = User::factory()->estudiante($estudiante)->create();

    $this->actingAs($user, 'student')
        ->patch(route('estudiante.profile.update'), [
            'username' => 'luis_mendoza_nuevo',
            'telefono' => '72222222',
            'current_password' => 'password',
            'password' => 'nueva-password-segura',
            'password_confirmation' => 'nueva-password-segura',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('estudiante.profile.edit'));

    expect(Hash::check('nueva-password-segura', $user->fresh()->password))->toBeTrue()
        ->and($user->fresh()->username)->toBe('luis_mendoza_nuevo')
        ->and($estudiante->fresh()->telefono)->toBe('72222222');
});

test('a docente can update their username without changing their role', function () {
    $docente = Docente::create([
        'nombres' => 'Betty',
        'apellido_paterno' => 'Cruz',
        'apellido_materno' => 'Rojas',
        'ci' => fake()->unique()->numerify('########'),
        'cargo' => 'Docente',
        'estado' => 'activo',
    ]);
    $user = User::factory()->docente($docente)->create([
        'username' => 'betty_anterior',
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'username' => 'betty_nueva',
            'nombres' => $docente->nombres,
            'apellido_paterno' => $docente->apellido_paterno,
            'apellido_materno' => $docente->apellido_materno,
            'ci' => $docente->ci,
            'cargo' => $docente->cargo,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->username)->toBe('betty_nueva')
        ->and($user->username_cambiado_en)->not->toBeNull()
        ->and($user->tipo_usuario)->toBe('docente');
});

test('a student sees the student portal dashboard after logging in', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Luis',
        'apellido_paterno' => 'Rojas',
        'apellido_materno' => 'Vargas',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 2,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $user = User::factory()->estudiante($estudiante)->create();

    $this->actingAs($user)
        ->get(route('estudiante.dashboard'))
        ->assertOk()
        ->assertSee('Portal del estudiante')
        ->assertSee($estudiante->matricula)
        ->assertSee('Solicitar equipo');
});

test('a student who must change their password can update it and access the portal', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Sofia',
        'apellido_paterno' => 'Mamani',
        'apellido_materno' => 'Rojas',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 2,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $user = User::factory()->estudiante($estudiante)->create([
        'debe_cambiar_password' => true,
    ]);

    $this->actingAs($user)
        ->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'nueva-password-segura',
            'password_confirmation' => 'nueva-password-segura',
        ])
        ->assertSessionHas('status', 'password-updated');

    expect(Hash::check('nueva-password-segura', $user->fresh()->password))->toBeTrue()
        ->and($user->fresh()->debe_cambiar_password)->toBeFalse();

    $this->actingAs($user->fresh())
        ->get(route('estudiante.dashboard'))
        ->assertOk()
        ->assertSee('Portal del estudiante');
});

test('the student logout returns to the student login portal', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Mario',
        'apellido_paterno' => 'Flores',
        'apellido_materno' => 'Cruz',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 1,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $user = User::factory()->estudiante($estudiante)->create();

    $this->actingAs($user)
        ->post(route('estudiante.logout'))
        ->assertRedirect(route('estudiante.login'));

    $this->assertGuest();
});

test('the shared logout route respects the student portal', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Rosa',
        'apellido_paterno' => 'Perez',
        'apellido_materno' => 'Lopez',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 4,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $user = User::factory()->estudiante($estudiante)->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('estudiante.login'));

    $this->assertGuest();
});

test('a student is redirected to the student login after resetting their password', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Elena',
        'apellido_paterno' => 'Gomez',
        'apellido_materno' => 'Soto',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 2,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $user = User::factory()->estudiante($estudiante)->create();

    $this->withSession([
        'password_reset_user_id' => $user->id,
        'password_reset_expires' => now()->addMinutes(10),
    ])->post(route('password.security.reset'), [
        'password' => 'NuevaPassword123!',
        'password_confirmation' => 'NuevaPassword123!',
    ])->assertRedirect(route('estudiante.login'));

    expect(Hash::check('NuevaPassword123!', $user->fresh()->password))->toBeTrue();
});

test('student security answers remain case insensitive for legacy hashes', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Laura',
        'apellido_paterno' => 'Vega',
        'apellido_materno' => 'Flores',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 3,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $user = User::factory()->estudiante($estudiante)->create();

    foreach (['Azul', 'Respuesta Dos', 'Tercera'] as $index => $respuesta) {
        $user->preguntasSeguridad()->create([
            'pregunta' => 'Pregunta '.($index + 1),
            'respuesta_hash' => Hash::make($respuesta),
        ]);
    }

    $this->withSession([
        'password_reset_user_id' => $user->id,
        'password_reset_expires' => now()->addMinutes(10),
    ])->post(route('password.security.answers'), [
        'respuesta' => ['azul', 'respuesta dos', 'tercera'],
    ])->assertRedirect(route('estudiante.password.security.reset.show'));
});

test('a student with a non-active profile cannot reset their password', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mendoza',
        'apellido_materno' => 'Rios',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 1,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'congelado',
    ]);
    $user = User::factory()->estudiante($estudiante)->create();

    $this->withSession([
        'password_reset_user_id' => $user->id,
        'password_reset_expires' => now()->addMinutes(10),
    ])->post(route('password.security.reset'), [
        'password' => 'NuevaPassword123!',
        'password_confirmation' => 'NuevaPassword123!',
    ])->assertRedirect(route('password.request'))
        ->assertSessionHasErrors('username');
});

test('student password recovery shows independent visibility controls', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Visibilidad',
        'apellido_paterno' => 'Prueba',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 1,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $user = User::factory()->estudiante($estudiante)->create();

    $this->withSession([
        'password_reset_user_id' => $user->id,
        'password_reset_expires' => now()->addMinutes(10),
    ])->get(route('estudiante.password.security.reset.show'))
        ->assertOk()
        ->assertSee('nuevaVisible')
        ->assertSee('confirmacionVisible')
        ->assertSee('Mostrar nueva contraseña')
        ->assertSee('Mostrar confirmación');
});

<?php

use App\Models\Docente;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('profile page is displayed', function () {
    $user = createProfileUser();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('administrator can view and update their own profile and account details', function () {
    Storage::fake('public');
    $admin = createProfileAdminUser();
    $otherAdmin = createProfileAdminUser();

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertSee('Perfil del administrador')
        ->assertSee('name="username"', false)
        ->assertSee('name="fecha_ingreso"', false)
        ->assertSee('name="current_password"', false);

    $this->actingAs($admin)->patch(route('profile.update'), [
        'name' => 'Cuenta administrativa actualizada',
        'username' => 'admin-actualizado',
        'nombres' => 'Nombre actualizado',
        'apellido_paterno' => 'Paterno actualizado',
        'apellido_materno' => 'Materno actualizado',
        'ci' => '12345678',
        'ci_extension' => 'SC',
        'telefono' => '70000000',
        'cargo' => 'Administrador general',
        'nivel_academico' => 'Licenciatura',
        'foto_personal' => UploadedFile::fake()->image('admin.jpg'),
        'docente_id' => $otherAdmin->docente_id,
        'tipo_usuario' => 'docente',
        'estado' => 'inactivo',
    ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'profile-updated')
        ->assertRedirect(route('profile.edit'));

    $admin->refresh();
    expect($admin->name)->toBe('Cuenta administrativa actualizada');
    expect($admin->username)->toBe('admin-actualizado');
    expect($admin->tipo_usuario)->toBe('administrador');
    expect($admin->docente_id)->toBe($admin->docente->id);
    expect($admin->docente->nombres)->toBe('Nombre actualizado');
    expect($admin->docente->apellido_paterno)->toBe('Paterno actualizado');
    expect($admin->docente->apellido_materno)->toBe('Materno actualizado');
    expect($admin->docente->ci_extension)->toBe('SC');
    expect($admin->docente->estado)->toBe('activo');
    Storage::disk('public')->assertExists($admin->docente->foto_personal);
    expect($otherAdmin->fresh()->docente->nombres)->toBe('Administrador');
});

test('administrator can change their own password with the current password', function () {
    $admin = createProfileAdminUser();

    $this->actingAs($admin)
        ->from(route('profile.edit'))
        ->put(route('password.update'), [
            'current_password' => 'password-inicial',
            'password' => 'nueva-password-segura',
            'password_confirmation' => 'nueva-password-segura',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'password-updated')
        ->assertRedirect(route('profile.edit'));

    expect(Hash::check('nueva-password-segura', $admin->fresh()->password))->toBeTrue();
});

test('profile information can be updated', function () {
    Storage::fake('public');
    $user = createProfileUser();
    $otherUser = createProfileUser();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'username' => 'test-user',
            'nombres' => 'Nombre actualizado',
            'apellido_paterno' => 'Apellido actualizado',
            'apellido_materno' => 'Segundo apellido',
            'ci' => '12345678',
            'ci_extension' => 'LP',
            'telefono' => '70000000',
            'cargo' => 'Docente actualizado',
            'nivel_academico' => 'Licenciatura',
            'foto_personal' => UploadedFile::fake()->image('docente.jpg'),
            'docente_id' => $otherUser->docente_id,
            'estado' => 'inactivo',
            'fecha_ingreso' => '2000-01-01',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test-user', $user->username);
    $this->assertSame('Nombre actualizado', $user->docente->nombres);
    $this->assertSame('Apellido actualizado', $user->docente->apellido_paterno);
    $this->assertSame('12345678', $user->docente->ci);
    $this->assertSame('LP', $user->docente->ci_extension);
    $this->assertSame('70000000', $user->docente->telefono);
    $this->assertSame('activo', $user->docente->estado);
    $this->assertSame('2000-01-01', $user->docente->fecha_ingreso?->format('Y-m-d'));
    Storage::disk('public')->assertExists($user->docente->foto_personal);
    $this->assertSame('Docente', $otherUser->docente->nombres);
});

test('a docente can update their fecha de ingreso', function () {
    $user = createProfileUser();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'nombres' => $user->docente->nombres,
            'apellido_paterno' => $user->docente->apellido_paterno,
            'apellido_materno' => $user->docente->apellido_materno,
            'ci' => $user->docente->ci,
            'ci_extension' => 'LP',
            'telefono' => '70000000',
            'correo_personal' => 'docente@example.com',
            'cargo' => $user->docente->cargo,
            'especialidad' => 'Sistemas',
            'nivel_academico' => 'Licenciatura',
            'fecha_ingreso' => '2018-03-15',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->docente->fecha_ingreso?->format('Y-m-d'))->toBe('2018-03-15');
});

test('username must be unique across accounts', function () {
    $user = createProfileUser();
    $existingUser = createProfileUser();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->patch('/profile', [
            'name' => $user->name,
            'username' => $existingUser->username,
        ]);

    $response
        ->assertSessionHasErrors('username')
        ->assertRedirect('/profile');

    $this->assertSame($user->username, $user->refresh()->username);
});

test('users cannot delete their own account from profile settings', function () {
    $user = createProfileUser();

    $response = $this
        ->actingAs($user)
        ->delete('/profile');

    $response->assertStatus(405);
    $this->assertNotNull($user->fresh());
});

function createProfileUser(): User
{
    $docente = Docente::create([
        'nombres' => 'Docente',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Perfil',
        'ci' => fake()->unique()->numerify('########'),
        'cargo' => 'Docente',
        'estado' => 'activo',
    ]);

    return User::create([
        'name' => 'Usuario de prueba',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => 'docente',
        'docente_id' => $docente->id,
    ]);
}

function createProfileAdminUser(): User
{
    $docente = Docente::create([
        'nombres' => 'Administrador',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Perfil',
        'ci' => fake()->unique()->numerify('########'),
        'ci_extension' => 'LP',
        'cargo' => 'Administrador',
        'estado' => 'activo',
    ]);

    return User::create([
        'name' => 'Cuenta administrativa',
        'username' => fake()->unique()->userName(),
        'password' => 'password-inicial',
        'tipo_usuario' => 'administrador',
        'docente_id' => $docente->id,
    ]);
}

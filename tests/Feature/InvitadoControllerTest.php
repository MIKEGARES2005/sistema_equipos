<?php

use App\Models\Docente;
use App\Models\Invitado;
use App\Models\User;

test('administrators can create and filter invitados', function () {
    $admin = createInvitadoAdminActor();

    $this->actingAs($admin)
        ->get(route('invitados.create'))
        ->assertOk()
        ->assertSee('name="ci_extension"', false)
        ->assertSee('value="LP"', false)
        ->assertSee('value="OR"', false)
        ->assertSee('value="PT"', false)
        ->assertSee('value="CB"', false)
        ->assertSee('value="SC"', false)
        ->assertSee('value="TJ"', false)
        ->assertSee('value="CH"', false)
        ->assertSee('value="BE"', false)
        ->assertSee('value="PD"', false);

    $response = $this->actingAs($admin)->post(route('invitados.store'), [
        'tipo' => 'externo',
        'nombres' => 'María',
        'apellido_paterno' => 'Vega',
        'apellido_materno' => 'Rojas',
        'ci' => '12345678',
        'ci_extension' => 'LP',
        'telefono' => '70000000',
        'procedencia' => 'Instituto invitado',
        'estado' => 'activo',
    ]);

    $response->assertRedirect(route('invitados.index'));
    $invitado = Invitado::where('ci', '12345678')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('invitados.index', ['buscar' => 'vega']))
        ->assertOk()
        ->assertSee($invitado->nombreCompleto());
});

test('administrators can view and edit an invitado', function () {
    $admin = createInvitadoAdminActor();
    $invitado = Invitado::create([
        'tipo' => 'estudiante',
        'nombres' => 'Luis',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Invitado',
        'ci' => '87654321',
        'semestre' => 2,
        'estado' => 'activo',
    ]);

    $this->actingAs($admin)
        ->get(route('invitados.show', $invitado))
        ->assertOk()
        ->assertSee('Luis Prueba Invitado');

    $this->actingAs($admin)
        ->put(route('invitados.update', $invitado), [
            'tipo' => 'docente',
            'nombres' => 'Luis Eduardo',
            'apellido_paterno' => 'Actualizado',
            'apellido_materno' => 'Invitado',
            'ci' => '87654321',
            'estado' => 'inactivo',
        ])
        ->assertRedirect(route('invitados.show', $invitado));

    $this->assertDatabaseHas('invitados', [
        'id' => $invitado->id,
        'nombres' => 'Luis Eduardo',
        'apellido_paterno' => 'Actualizado',
        'estado' => 'inactivo',
    ]);
});

test('administrators can archive an invitado', function () {
    $admin = createInvitadoAdminActor();
    $invitado = Invitado::create([
        'tipo' => 'externo',
        'nombres' => 'Invitado',
        'apellido_paterno' => 'Archivado',
        'ci' => '11223344',
        'estado' => 'activo',
    ]);

    $this->actingAs($admin)
        ->delete(route('invitados.destroy', $invitado))
        ->assertRedirect(route('invitados.index'));

    $this->assertSoftDeleted('invitados', ['id' => $invitado->id]);
});

function createInvitadoAdminActor(): User
{
    $docente = Docente::create([
        'nombres' => 'Administrador',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Sistema',
        'ci' => '99887766',
        'cargo' => 'Administrador',
        'estado' => 'activo',
    ]);

    return User::create([
        'username' => 'admin_invitados',
        'password' => 'password',
        'tipo_usuario' => 'administrador',
        'docente_id' => $docente->id,
    ]);
}

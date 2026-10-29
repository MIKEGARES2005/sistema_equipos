<?php

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\PreguntaSeguridad;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('administrators only see their own security questions and never see saved answers', function () {
    $administrator = createSecurityQuestionUser('administrador');
    $otherUser = createSecurityQuestionUser('docente');

    $administrator->preguntasSeguridad()->create([
        'pregunta' => 'Pregunta privada del administrador',
        'respuesta_hash' => Hash::make('respuesta privada admin'),
    ]);
    $otherUser->preguntasSeguridad()->create([
        'pregunta' => 'Pregunta privada del docente',
        'respuesta_hash' => Hash::make('respuesta privada docente'),
    ]);

    $this->actingAs($administrator)
        ->get(route('security.questions.edit'))
        ->assertSee('Pregunta privada del administrador')
        ->assertDontSee('Pregunta privada del docente')
        ->assertDontSee('respuesta privada admin')
        ->assertDontSee('respuesta privada docente');
});

test('incorrect recovery answers are shown as a popup alert', function () {
    $administrator = createSecurityQuestionUser('administrador');

    foreach (range(1, 3) as $numero) {
        $administrator->preguntasSeguridad()->create([
            'pregunta' => "Pregunta {$numero}",
            'respuesta_hash' => Hash::make("Respuesta {$numero}"),
        ]);
    }

    $this->withSession([
        'password_reset_user_id' => $administrator->id,
        'password_reset_expires' => now()->addMinutes(10),
    ])->post(route('password.security.answers'), [
        'respuesta' => ['incorrecta', 'incorrecta', 'incorrecta'],
    ])->assertRedirect(route('password.security.questions'));

    $this->get(route('password.security.questions'))
        ->assertOk()
        ->assertSee('Una o más respuestas son incorrectas.');
});

test('administrators can replace their three security questions without changing another account', function () {
    $administrator = createSecurityQuestionUser('administrador');
    $otherUser = createSecurityQuestionUser('docente');
    $otherQuestion = $otherUser->preguntasSeguridad()->create([
        'pregunta' => 'Pregunta que no debe cambiar',
        'respuesta_hash' => Hash::make('respuesta de otro usuario'),
    ]);

    $response = $this->actingAs($administrator)
        ->put(route('security.questions.update'), [
            'current_password' => 'password-segura',
            'preguntas' => [
                ['pregunta' => 'Nombre de mi primera mascota', 'respuesta' => 'Luna Azul'],
                ['pregunta' => 'Ciudad de nacimiento', 'respuesta' => 'Villa Norte'],
                ['pregunta' => 'Comida favorita', 'respuesta' => 'Sopa de mani'],
            ],
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Preguntas de seguridad actualizadas correctamente.');

    $preguntasAdministrador = PreguntaSeguridad::query()
        ->where('user_id', $administrator->id)
        ->orderBy('id')
        ->get();

    expect($preguntasAdministrador)->toHaveCount(3);
    expect($preguntasAdministrador[0]->pregunta)->toBe('¿Nombre de mi primera mascota?');
    expect(Hash::check('luna azul', $preguntasAdministrador[0]->respuesta_hash))->toBeTrue();
    expect($preguntasAdministrador[0]->respuesta_hash)->not->toBe('Luna Azul');

    $this->assertModelExists($otherQuestion);
    expect($otherQuestion->fresh()->pregunta)->toBe('Pregunta que no debe cambiar');
});

test('incomplete security question updates do not remove the saved questions', function () {
    $administrator = createSecurityQuestionUser('administrador');
    $savedQuestion = $administrator->preguntasSeguridad()->create([
        'pregunta' => 'Pregunta existente',
        'respuesta_hash' => Hash::make('respuesta existente'),
    ]);

    $this->actingAs($administrator)
        ->put(route('security.questions.update'), [
            'current_password' => 'password-segura',
            'preguntas' => [
                ['pregunta' => 'Solo una pregunta', 'respuesta' => 'Respuesta incompleta'],
            ],
        ])
        ->assertSessionHasErrors('preguntas');

    $this->assertModelExists($savedQuestion);
});

test('students can manage their own security questions', function () {
    $estudiante = Estudiante::create([
        'nombres' => 'Estudiante',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Seguridad',
        'matricula' => fake()->unique()->numerify('MAT#####'),
        'semestre' => 1,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => 'activo',
    ]);
    $user = User::factory()->estudiante($estudiante)->create();

    $this->actingAs($user)
        ->get(route('security.questions.edit'))
        ->assertOk();

    $this->actingAs($user)
        ->put(route('security.questions.update'), [
            'current_password' => 'password',
            'preguntas' => [
                ['pregunta' => 'Pregunta uno', 'respuesta' => 'Respuesta uno'],
                ['pregunta' => 'Pregunta dos', 'respuesta' => 'Respuesta dos'],
                ['pregunta' => 'Pregunta tres', 'respuesta' => 'Respuesta tres'],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($user->preguntasSeguridad()->count())->toBe(3);
});

function createSecurityQuestionUser(string $tipoUsuario): User
{
    $docente = Docente::create([
        'nombres' => 'Docente',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Seguridad',
        'ci' => fake()->unique()->numerify('########'),
        'cargo' => 'Docente',
        'estado' => 'activo',
    ]);

    return User::create([
        'name' => 'Cuenta '.$tipoUsuario,
        'username' => fake()->unique()->userName(),
        'password' => 'password-segura',
        'tipo_usuario' => $tipoUsuario,
        'docente_id' => $docente->id,
    ]);
}

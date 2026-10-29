<?php

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Gestion;
use App\Models\Materia;
use App\Models\Notificacion;
use App\Models\SolicitudRegistro;
use App\Models\User;
use App\Services\CloudflareTunnelService;
use App\Services\EstudiantePortalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertSee('¿Olvidó su contraseña?');
});

test('staff login exposes the student QR access', function () {
    config(['services.estudiante_portal.url' => null]);

    $this->get('/login')
        ->assertOk()
        ->assertSee('Acceso Estudiantil (Código QR)')
        ->assertSee('Túnel pendiente')
        ->assertSee('ESTUDIANTE_PORTAL_URL');
});

test('student login screen can be rendered', function () {
    $this->get(route('estudiante.login'))
        ->assertOk()
        ->assertSee('Usuario o matrícula')
        ->assertSee('¿Eres nuevo en el sistema? Regístrate aquí')
        ->assertSee(route('estudiante.password.request'), false);
});

test('administrators can open the student login without being redirected to the admin dashboard', function () {
    $admin = createAuthUser('administrador');

    $this->actingAs($admin)
        ->get(route('estudiante.login'))
        ->assertOk()
        ->assertViewIs('auth.login-estudiante');
});

test('a valid student login keeps the administrative session independent', function () {
    $admin = createAuthUser('administrador');
    $student = createAuthUser('estudiante');

    $response = $this->actingAs($admin)->post(route('estudiante.login.store'), [
        'username' => $student->username,
        'password' => 'password',
    ]);

    expect(Auth::guard('web')->user()->is($admin))->toBeTrue()
        ->and(Auth::guard('student')->user()->is($student))->toBeTrue();
    $response->assertRedirect(route('estudiante.dashboard', absolute: false));
});

test('student logout does not close the administrative session', function () {
    $admin = createAuthUser('administrador');
    $student = createAuthUser('estudiante');

    $this->actingAs($admin)->post(route('estudiante.login.store'), [
        'username' => $student->username,
        'password' => 'password',
    ]);

    $this->post(route('estudiante.logout'))
        ->assertRedirect(route('estudiante.login'));

    expect(Auth::guard('web')->user()->is($admin))->toBeTrue()
        ->and(Auth::guard('student')->check())->toBeFalse();
});

test('students can open their portal dashboard', function () {
    $user = createAuthUser('estudiante');

    $this->actingAs($user, 'student')
        ->get(route('estudiante.dashboard'))
        ->assertOk()
        ->assertSee('Portal del estudiante')
        ->assertSee(route('estudiante.security.questions.edit'), false);
});

test('students can open the detailed notification tab', function () {
    $student = createAuthUser('estudiante');
    $student->notificaciones()->create([
        'tipo' => 'sistema',
        'titulo' => 'Aviso de prueba',
        'mensaje' => 'Este es un detalle completo de la notificación.',
    ]);

    $this->actingAs($student, 'student')
        ->get(route('estudiante.notifications.details'))
        ->assertOk()
        ->assertSee('Bandeja completa')
        ->assertSee('Aviso del sistema')
        ->assertSee('Este es un detalle completo de la notificación.')
        ->assertSee('registra tus preguntas de seguridad desde tu perfil')
        ->assertSee(route('estudiante.notifications.clear'), false);
});

test('students who must change their password can still open notifications', function () {
    $student = createAuthUser('estudiante');
    $student->update(['debe_cambiar_password' => true]);

    $this->actingAs($student, 'student')
        ->get(route('estudiante.notifications.details'))
        ->assertOk()
        ->assertViewIs('estudiante.notifications');
});

test('student password recovery opens the security-question flow', function () {
    $this->get(route('estudiante.password.request'))
        ->assertOk()
        ->assertSee('Recuperar contraseña')
        ->assertSee(route('estudiante.password.security.verify'), false);
});

test('an administrative session stays in the student recovery flow', function () {
    $admin = createAuthUser('administrador');
    $student = createAuthUser('estudiante');

    foreach (['Color favorito', 'Ciudad de nacimiento', 'Nombre de mascota'] as $pregunta) {
        $student->preguntasSeguridad()->create([
            'pregunta' => $pregunta,
            'respuesta_hash' => Hash::make('respuesta'),
        ]);
    }

    $this->actingAs($admin)
        ->get(route('estudiante.password.request'))
        ->assertOk()
        ->assertViewIs('auth.estudiante.forgot-password');

    $this->actingAs($admin)
        ->post(route('estudiante.password.security.verify'), [
            'username' => $student->username,
        ])
        ->assertRedirect(route('estudiante.password.security.questions'));
});

test('student QR page uses the configured public portal URL', function () {
    config(['services.estudiante_portal.url' => 'https://portal.example.com']);

    $this->get(route('estudiante.qr'))
        ->assertOk()
        ->assertSee('https://portal.example.com/estudiante/login')
        ->assertSee('api.qrserver.com');
});

test('staff login reads the public URL from the cloudflared log', function () {
    $logPath = tempnam(sys_get_temp_dir(), 'cloudflared-log-');
    file_put_contents($logPath, 'INF | https://qr-log-test.trycloudflare.com |');

    config([
        'services.estudiante_portal.url' => null,
        'services.cloudflare.log_path' => $logPath,
    ]);

    $this->partialMock(CloudflareTunnelService::class, function ($mock) {
        $mock->shouldReceive('isRunning')->andReturn(true);
    });

    $this->get('/login')
        ->assertOk()
        ->assertSee('https://qr-log-test.trycloudflare.com/estudiante/login')
        ->assertSee('api.qrserver.com');

    @unlink($logPath);
});

test('a tunnel URL is ignored when cloudflared is not running', function () {
    $logPath = tempnam(sys_get_temp_dir(), 'cloudflared-log-');
    file_put_contents($logPath, 'INF | https://stopped-test.trycloudflare.com |');

    config([
        'services.estudiante_portal.url' => null,
        'services.cloudflare.log_path' => $logPath,
    ]);

    $this->partialMock(CloudflareTunnelService::class, function ($mock) {
        $mock->shouldReceive('isRunning')->andReturn(false);
    });

    $status = app(EstudiantePortalService::class)->connectionStatus();

    expect($status['available'])->toBeFalse()
        ->and($status['starting'])->toBeFalse()
        ->and(Cache::has(CloudflareTunnelService::UNAVAILABLE_CACHE_KEY))->toBeTrue();

    @unlink($logPath);
});

test('tunnel reconnection is forbidden from public addresses', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
        ->post(route('estudiante.qr.reconnect'))
        ->assertForbidden();
});

test('student QR page shows the pending tunnel state without a public URL', function () {
    config(['services.estudiante_portal.url' => null]);

    $this->get(route('estudiante.qr'))
        ->assertOk()
        ->assertSee('QR pendiente de configuración')
        ->assertSee('ESTUDIANTE_PORTAL_URL');
});

test('student QR image can be downloaded', function () {
    config(['services.estudiante_portal.url' => 'https://portal.example.com']);

    Http::fake([
        'api.qrserver.com/*' => Http::response('fake-png', 200, ['Content-Type' => 'image/png']),
    ]);

    $this->get(route('estudiante.qr.download'))
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml; charset=UTF-8')
        ->assertHeader('content-disposition', 'attachment; filename="qr-acceso-estudiantil.svg"')
        ->assertSee('ACCESO ESTUDIANTIL')
        ->assertSee('data:image/png;base64,ZmFrZS1wbmc=');
});

test('password recovery opens the username and security-question flow', function () {
    $this->get('/forgot-password')
        ->assertOk()
        ->assertSee('Ingresa tu nombre de usuario');
});

test('public student registration assumes first semester', function () {
    $this->get(route('registro.aspirante.create'))
        ->assertOk()
        ->assertDontSee('Semestre de ingreso')
        ->assertDontSee('Generación de ingreso')
        ->assertSee('nombre@itibb.edu.bo');
});

test('only administrators can view the security audit log', function () {
    $administrador = createAuthUser('administrador');

    $this->actingAs($administrador)
        ->get(route('admin.bitacora.index'))
        ->assertOk()
        ->assertSee('Bitácora del sistema');

    $estudiante = createAuthUser('estudiante');

    $this->actingAs($estudiante)
        ->get(route('admin.bitacora.index'))
        ->assertForbidden();
});

test('active docentes can authenticate using the login screen', function () {
    $docente = createAuthDocente();
    $user = createAuthUser('docente', $docente);

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('active administradores with an active docente profile can authenticate', function () {
    $docente = createAuthDocente();
    $user = createAuthUser('administrador', $docente);

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('administrators with a docente profile still receive the administrative dashboard', function () {
    $docente = createAuthDocente();
    $user = createAuthUser('administrador', $docente);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Personas y usuarios')
        ->assertDontSee('Panel docente');
});

test('students cannot authenticate through the staff login', function () {
    $user = createAuthUser('estudiante');

    $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('active students can authenticate through the student login', function () {
    $user = createAuthUser('estudiante');

    $response = $this->post('/estudiante/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user, 'student');
    $response->assertRedirect(route('estudiante.dashboard', absolute: false));
});

test('new students see the welcome modal on the first dashboard access', function () {
    $user = createAuthUser('estudiante');
    $user->update(['debe_cambiar_password' => true]);

    $response = $this->post('/estudiante/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $response
        ->assertRedirect(route('estudiante.dashboard', absolute: false))
        ->assertSessionHas('student_welcome', true);
});

test('account lock events notify admins and stay in the audit log', function () {
    $user = createAuthUser('administrador');

    foreach (range(1, 5) as $intento) {
        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password-incorrecta',
        ]);
    }

    expect(Notificacion::query()->where('tipo', 'seguridad')->where('titulo', 'Cuenta bloqueada por intentos fallidos')->exists())->toBeTrue();
    $this->assertDatabaseHas('bitacora', [
        'accion' => 'login_fallido',
        'entidad' => 'users',
        'entidad_id' => $user->id,
    ]);
});

test('locked accounts show a countdown instead of the login form', function () {
    $user = createAuthUser('administrador');

    foreach (range(1, 5) as $intento) {
        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password-incorrecta',
        ]);
    }

    $this->get('/login')
        ->assertOk()
        ->assertSee('Cuenta bloqueada temporalmente')
        ->assertSee('Puedes volver a intentarlo en');
});

test('staff cannot authenticate through the student login', function () {
    $user = createAuthUser('docente');

    $this->post('/estudiante/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('students whose profile is not active cannot authenticate', function (string $estado) {
    $user = User::factory()
        ->estudiante(createAuthEstudiante($estado))
        ->create();

    $this->post('/estudiante/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertGuest();
})->with(['inactivo', 'egresado', 'retirado', 'congelado']);

test('guest students are redirected to the student login', function () {
    $this->get(route('estudiante.dashboard'))
        ->assertRedirect(route('estudiante.login'));
});

test('administradores without a docente profile can authenticate', function () {
    $user = createAuthUser('administrador');

    $response = $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('docentes with an inactive profile cannot authenticate', function () {
    $docente = createAuthDocente('inactivo');
    $user = createAuthUser('docente', $docente);

    $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('students cannot access the staff dashboard through an existing session', function () {
    $user = createAuthUser('estudiante');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('students cannot access staff-only sections', function (string $routeName) {
    $user = createAuthUser('estudiante');

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertForbidden();
})->with([
    'gestion-academica.index',
    'panel-docente.estudiantes',
    'admin.solicitudes.index',
    'admin.bitacora.index',
    'usuarios.index',
    'equipos.index',
]);

test('administrators see registration requests inside the admin shell', function () {
    $admin = createAuthUser('administrador');

    $this->actingAs($admin)
        ->get(route('admin.solicitudes.index'))
        ->assertOk()
        ->assertSee('Personas y usuarios')
        ->assertSee('Solicitudes de registro')
        ->assertSee('Solicitudes de estudiantes');
});

test('administrators can open and register student follow-up events', function () {
    $admin = createAuthUser('administrador');
    $student = createAuthUser('estudiante');

    $this->actingAs($admin)
        ->get(route('seguimiento-estudiantil.index'))
        ->assertOk()
        ->assertSee('Seguimiento estudiantil')
        ->assertSee($student->estudiante->nombres);

    $this->actingAs($admin)
        ->post(route('seguimiento-estudiantil.events.store', $student->estudiante), [
            'tipo_evento' => 'observacion',
            'fecha_evento' => '2026-10-29',
            'descripcion' => 'Seguimiento inicial del estudiante.',
        ])
        ->assertRedirect(route('seguimiento-estudiantil.index', absolute: false));

    $this->assertDatabaseHas('historial_seguimiento_estudiantes', [
        'estudiante_id' => $student->estudiante_id,
        'tipo_evento' => 'observacion',
        'descripcion' => 'Seguimiento inicial del estudiante.',
        'registrado_por' => $admin->id,
    ]);
});

test('student follow-up details expose the academic summary and freeze rules', function () {
    $admin = createAuthUser('administrador');
    $student = createAuthUser('estudiante');

    $this->actingAs($admin)
        ->get(route('seguimiento-estudiantil.show', $student->estudiante))
        ->assertOk()
        ->assertSee('Materias, docentes y notas')
        ->assertSee('Registrar evento')
        ->assertSee('Congelamiento');

    $this->actingAs($admin)
        ->post(route('seguimiento-estudiantil.events.store', $student->estudiante), [
            'tipo_evento' => 'congelamiento',
            'fecha_evento' => '2026-10-29',
            'descripcion' => 'Suspensión temporal documentada.',
        ])
        ->assertRedirect(route('seguimiento-estudiantil.index', absolute: false));

    expect($student->estudiante->fresh()->estado)->toBe('congelado');
    expect($student->fresh()->activo)->toBeFalse();

    $this->actingAs($admin)
        ->post(route('seguimiento-estudiantil.events.store', $student->estudiante), [
            'tipo_evento' => 'reincorporacion',
            'fecha_evento' => '2026-10-29',
        ])
        ->assertRedirect(route('seguimiento-estudiantil.index', absolute: false));

    expect($student->estudiante->fresh()->estado)->toBe('activo');
    expect($student->fresh()->activo)->toBeTrue();
});

test('retirement is rejected unless the student has all fourth semester subjects approved', function () {
    $admin = createAuthUser('administrador');
    $student = createAuthUser('estudiante');

    $this->actingAs($admin)
        ->post(route('seguimiento-estudiantil.events.store', $student->estudiante), [
            'tipo_evento' => 'retiro',
            'fecha_evento' => '2026-10-29',
            'descripcion' => 'Intento de retiro.',
        ])
        ->assertSessionHasErrors('tipo_evento');

    expect($student->estudiante->fresh())->not->toBeNull();
});

test('administrators can filter the security audit log by role', function () {
    $admin = createAuthUser('administrador');

    $this->actingAs($admin)
        ->get(route('admin.bitacora.index', ['rol' => 'administrador']))
        ->assertOk()
        ->assertSee('Filtrar actividad')
        ->assertSee('Todos los roles');
});

test('administrators can poll their notification tray', function () {
    $admin = createAuthUser('administrador');
    Notificacion::create([
        'user_id' => $admin->id,
        'tipo' => 'solicitud',
        'titulo' => 'Nueva solicitud de registro',
        'mensaje' => 'Un estudiante envió una solicitud.',
        'url' => route('admin.solicitudes.index', ['estado' => 'pendiente']),
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.notifications.index'))
        ->assertOk()
        ->assertJsonPath('no_leidas', 1)
        ->assertJsonPath('notificaciones.0.titulo', 'Nueva solicitud de registro');

    $this->actingAs($admin)
        ->postJson(route('admin.notifications.clear'))
        ->assertOk()
        ->assertJsonPath('no_leidas', 0)
        ->assertJsonPath('notificaciones', []);
});

test('students can poll and clear only their own notification tray', function () {
    $student = createAuthUser('estudiante');
    $admin = createAuthUser('administrador');
    Notificacion::create([
        'user_id' => $student->id,
        'tipo' => 'asignacion',
        'titulo' => 'Equipo asignado',
        'mensaje' => 'Tu solicitud de equipo fue asignada.',
    ]);
    Notificacion::create([
        'user_id' => $admin->id,
        'tipo' => 'solicitud',
        'titulo' => 'Aviso administrativo',
        'mensaje' => 'Solo para administración.',
    ]);

    $this->actingAs($student, 'student')
        ->getJson(route('estudiante.notifications.index'))
        ->assertOk()
        ->assertJsonPath('no_leidas', 1)
        ->assertJsonPath('notificaciones.0.titulo', 'Equipo asignado')
        ->assertJsonPath('notificaciones.0.resumen', 'Asignación aceptada')
        ->assertJsonPath('notificaciones.0.url', route('estudiante.notifications.details'));

    $this->actingAs($student, 'student')
        ->postJson(route('estudiante.notifications.clear'))
        ->assertOk()
        ->assertJsonPath('no_leidas', 0)
        ->assertJsonPath('notificaciones', []);

    expect(Notificacion::query()->where('user_id', $admin->id)->exists())->toBeTrue();
});

test('public student registrations notify administrators and enter the audit log', function () {
    $admin = createAuthUser('administrador');

    $response = $this->post(route('registro.aspirante.store'), [
        'nombres' => 'Nuevo',
        'apellido_paterno' => 'Estudiante',
        'apellido_materno' => 'Prueba',
        'ci' => '87654321',
        'ci_extension' => 'LP',
        'telefono' => '70000001',
        'matricula' => 'MAT-TEST-001',
        'generacion_ingreso' => 'I-2026',
        'correo_personal' => 'nuevo@example.test',
        'direccion' => 'Dirección de prueba',
    ]);

    $solicitud = SolicitudRegistro::query()->where('matricula', 'MAT-TEST-001')->firstOrFail();

    $response->assertRedirect(route('registro.aspirante.status', $solicitud->token_cancelacion));
    expect(Notificacion::query()->where('user_id', $admin->id)->where('tipo', 'solicitud')->exists())->toBeTrue();
    $this->assertDatabaseHas('bitacora', [
        'accion' => 'crear_solicitud_registro',
        'entidad' => 'solicitudes_registro',
        'entidad_id' => $solicitud->id,
    ]);
});

test('public student registration allows an optional matricula and assigns the current generation', function () {
    $response = $this->post(route('registro.aspirante.store'), [
        'nombres' => 'Sin',
        'apellido_paterno' => 'Matricula',
        'ci' => '87654322',
        'ci_extension' => 'LP',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $solicitud = SolicitudRegistro::query()->where('ci', '87654322')->firstOrFail();

    expect($solicitud->matricula)->toBeNull()
        ->and($solicitud->semestre)->toBe(1)
        ->and($solicitud->generacion_ingreso)->toMatch('/^(?:I|II)-\d{4}$/');
});

test('students cancelling a registration keep the request in the cancelled history', function () {
    $admin = createAuthUser('administrador');
    $solicitud = SolicitudRegistro::create([
        'nombres' => 'Solicitud',
        'apellido_paterno' => 'Cancelada',
        'ci' => '87654323',
        'ci_extension' => 'LP',
        'semestre' => 1,
        'generacion_ingreso' => 'I-2026',
        'estado' => 'pendiente',
        'token_cancelacion' => str_repeat('f', 64),
    ]);

    $this->post(route('registro.aspirante.cancel', $solicitud->token_cancelacion))
        ->assertRedirect(route('estudiante.login'))
        ->assertSessionHas('success', 'La solicitud fue cancelada correctamente.');

    $this->assertDatabaseHas('solicitudes_registro', [
        'id' => $solicitud->id,
        'estado' => 'cancelada',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.solicitudes.index', ['estado' => 'cancelada']))
        ->assertOk()
        ->assertSee('Solicitud Cancelada');
});

test('approved student registrations create an account and notify the student', function () {
    $admin = createAuthUser('administrador');
    $solicitud = SolicitudRegistro::create([
        'nombres' => 'Cuenta',
        'apellido_paterno' => 'Nueva',
        'apellido_materno' => 'Prueba',
        'ci' => '76543210',
        'ci_extension' => 'LP',
        'matricula' => 'MAT-TEST-002',
        'semestre' => 1,
        'generacion_ingreso' => 'I-2026',
        'estado' => 'pendiente',
        'token_cancelacion' => str_repeat('b', 64),
    ]);

    $response = $this->actingAs($admin)->post(route('admin.solicitudes.aprobar', $solicitud->id));

    $usuario = User::query()->where('username', 'cuenta_nueva')->firstOrFail();

    $response->assertRedirect(route('admin.solicitudes.credentials'));
    $this->assertDatabaseHas('users', [
        'id' => $usuario->id,
        'tipo_usuario' => 'estudiante',
        'debe_cambiar_password' => true,
    ]);
    $this->assertDatabaseHas('solicitudes_registro', [
        'id' => $solicitud->id,
        'estado' => 'aprobada',
        'usuario_creado_id' => $usuario->id,
    ]);
    expect(Notificacion::query()->where('user_id', $usuario->id)->where('tipo', 'solicitud_aprobada')->exists())->toBeTrue();

    expect(session('registro_credenciales.username'))->toBe('cuenta_nueva')
        ->and(session('registro_credenciales.password'))->toBe('nueva76543210');
    $passwordTemporal = session('registro_credenciales.password');

    $this->actingAs($admin)
        ->get(route('admin.solicitudes.credentials'))
        ->assertOk()
        ->assertSee('cuenta_nueva')
        ->assertSee('nueva76543210');

    $this->actingAs($admin)
        ->post(route('admin.solicitudes.credentials.clear'))
        ->assertRedirect(route('admin.solicitudes.index', absolute: false));

    expect(session('registro_credenciales'))->toBeNull();
    $this->post('/logout');

    $this->post('/estudiante/login', [
        'username' => $usuario->username,
        'password' => $passwordTemporal,
    ])
        ->assertRedirect(route('estudiante.dashboard', absolute: false))
        ->assertSessionHas('student_welcome', true);
});

test('an approved registration can start the student session from the waiting screen', function () {
    $admin = createAuthUser('administrador');
    $solicitud = SolicitudRegistro::create([
        'nombres' => 'Ingreso',
        'apellido_paterno' => 'Automatico',
        'ci' => '76543211',
        'matricula' => 'MAT-AUTO-001',
        'semestre' => 1,
        'generacion_ingreso' => 'I-2026',
        'estado' => 'pendiente',
        'token_cancelacion' => str_repeat('a', 64),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.solicitudes.aprobar', $solicitud->id));

    $this->post(route('registro.aspirante.login-approved', $solicitud->token_cancelacion))
        ->assertOk()
        ->assertJsonPath('redirect', route('estudiante.dashboard', absolute: false));

    $this->assertAuthenticated('student');
    expect(session('student_welcome'))->toBeTrue();
});

test('administrators can approve multiple pending registrations at once', function () {
    $admin = createAuthUser('administrador');
    $solicitudes = collect([
        ['nombres' => 'Ana', 'ci' => '70000001', 'matricula' => 'MAT-BULK-001'],
        ['nombres' => 'Luis', 'ci' => '70000002', 'matricula' => 'MAT-BULK-002'],
    ])->map(function (array $datos): SolicitudRegistro {
        return SolicitudRegistro::create([
            ...$datos,
            'apellido_paterno' => 'Prueba',
            'apellido_materno' => 'Registro',
            'semestre' => 1,
            'generacion_ingreso' => 'I-2026',
            'estado' => 'pendiente',
            'token_cancelacion' => str_repeat((string) random_int(1, 9), 64),
        ]);
    });

    $response = $this->actingAs($admin)->post(route('admin.solicitudes.aprobar-multiples'), [
        'ids' => $solicitudes->pluck('id')->all(),
    ]);

    $response->assertRedirect(route('admin.solicitudes.credentials'))
        ->assertSessionMissing('success');

    expect(session('registro_credenciales.items'))->toHaveCount(2);
    $solicitudes->each(function (SolicitudRegistro $solicitud): void {
        expect($solicitud->fresh()->estado)->toBe('aprobada');
    });
    expect(User::query()->whereIn('username', ['ana_prueba', 'luis_prueba'])->count())->toBe(2);
});

test('administrators can reject a registration without entering a reason', function () {
    $admin = createAuthUser('administrador');
    $solicitud = SolicitudRegistro::create([
        'nombres' => 'Solicitud',
        'apellido_paterno' => 'Rechazada',
        'ci' => '65432109',
        'matricula' => 'MAT-TEST-003',
        'semestre' => 1,
        'estado' => 'pendiente',
        'token_cancelacion' => str_repeat('c', 64),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.solicitudes.rechazar', $solicitud->id))
        ->assertRedirect(route('admin.solicitudes.index'))
        ->assertSessionHas('registro_resultado.tipo', 'rechazo')
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('solicitudes_registro', [
        'id' => $solicitud->id,
        'estado' => 'rechazada',
        'motivo_rechazo' => null,
    ]);
});

test('public registration rejects a person who already has a student record', function () {
    $estudiante = createAuthEstudiante();

    $this->post(route('registro.aspirante.store'), [
        'nombres' => $estudiante->nombres,
        'apellido_paterno' => $estudiante->apellido_paterno,
        'ci' => $estudiante->ci,
        'matricula' => $estudiante->matricula,
    ])
        ->assertSessionHasErrors(['ci', 'matricula']);
});

test('duplicate pending requests are rejected instead of creating a second student account', function () {
    $admin = createAuthUser('administrador');
    $estudiante = createAuthEstudiante();
    $solicitud = SolicitudRegistro::create([
        'nombres' => 'Duplicado',
        'apellido_paterno' => 'Pendiente',
        'ci' => $estudiante->ci,
        'matricula' => $estudiante->matricula,
        'semestre' => 1,
        'estado' => 'pendiente',
        'token_cancelacion' => str_repeat('d', 64),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.solicitudes.aprobar', $solicitud->id))
        ->assertRedirect(route('admin.solicitudes.index'))
        ->assertSessionHas('registro_resultado.tipo', 'rechazo');

    $this->assertDatabaseHas('solicitudes_registro', [
        'id' => $solicitud->id,
        'estado' => 'rechazada',
    ]);
    expect(User::query()->where('estudiante_id', $estudiante->id)->exists())->toBeFalse();
});

test('docentes receive a dashboard without administrative navigation', function () {
    $user = createAuthUser('docente', createAuthDocente());

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Panel docente')
        ->assertDontSee('Personas y usuarios');
});

test('docentes only see schedules assigned to their own profile', function () {
    $docente = createAuthDocente();
    $user = createAuthUser('docente', $docente);
    $otroDocente = createAuthDocente();
    $gestion = Gestion::create([
        'codigo' => '2026-1',
        'anio' => 2026,
        'periodo' => '1',
        'fecha_inicio' => now()->startOfYear(),
        'fecha_fin' => now()->endOfYear(),
        'activa' => true,
    ]);
    $materiaPropia = Materia::create([
        'nombre' => 'Materia propia',
        'sigla' => 'PROP-101',
        'semestre' => 1,
        'estado' => 'activo',
    ]);
    $materiaAjena = Materia::create([
        'nombre' => 'Materia ajena',
        'sigla' => 'AJEN-102',
        'semestre' => 2,
        'estado' => 'activo',
    ]);

    DB::table('horarios')->insert([
        [
            'gestion_id' => $gestion->id,
            'docente_id' => $docente->id,
            'materia_id' => $materiaPropia->id,
            'dia_semana' => 1,
            'hora_inicio' => '08:00:00',
            'hora_fin' => '10:00:00',
            'aula' => 'Aula 1',
            'estado' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'gestion_id' => $gestion->id,
            'docente_id' => $otroDocente->id,
            'materia_id' => $materiaAjena->id,
            'dia_semana' => 2,
            'hora_inicio' => '10:00:00',
            'hora_fin' => '12:00:00',
            'aula' => 'Aula 2',
            'estado' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Materia propia')
        ->assertDontSee('Materia ajena');
});

test('an active session is denied after the docente profile becomes inactive', function () {
    $docente = createAuthDocente();
    $user = createAuthUser('docente', $docente);

    $docente->update(['estado' => 'inactivo']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('users cannot authenticate with an invalid password', function () {
    $docente = createAuthDocente();
    $user = createAuthUser('docente', $docente);

    $this->post('/login', [
        'username' => $user->username,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('public registration is unavailable', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register')->assertNotFound();
});

test('users can logout', function () {
    $docente = createAuthDocente();
    $user = createAuthUser('docente', $docente);

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/login');
});

function createAuthDocente(string $estado = 'activo'): Docente
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

function createAuthUser(string $tipoUsuario, ?Docente $docente = null): User
{
    return match ($tipoUsuario) {
        'docente' => User::factory()->docente($docente ?? createAuthDocente())->create(),
        'estudiante' => User::factory()->estudiante(createAuthEstudiante())->create(),
        default => $docente === null
            ? User::factory()->administrador()->create()
            : User::factory()->create([
                'tipo_usuario' => 'administrador',
                'docente_id' => $docente->id,
            ]),
    };
}

function createAuthEstudiante(string $estado = 'activo'): Estudiante
{
    return Estudiante::create([
        'nombres' => 'Estudiante',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Sistema',
        'matricula' => fake()->unique()->numerify('MAT####'),
        'semestre' => 1,
        'ci' => fake()->unique()->numerify('########'),
        'estado' => $estado,
    ]);
}

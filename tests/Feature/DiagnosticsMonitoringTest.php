<?php

use App\Models\Diagnostico;
use App\Models\Docente;
use App\Models\Equipo;
use App\Models\Heartbeat;
use App\Models\User;

test('staff can see device connectivity based on the last ten minutes of heartbeat data', function () {
    $this->travelTo(now()->setDate(2026, 10, 16)->setTime(12, 0));
    $admin = createDiagnosticsMonitoringUser();
    $connectedEquipment = createDiagnosticsMonitoringEquipment('EQ-MON-CONECTADO');
    $staleEquipment = createDiagnosticsMonitoringEquipment('EQ-MON-DESACTUALIZADO');
    createDiagnosticsMonitoringEquipment('EQ-MON-SIN-DATOS');

    $connectedHeartbeat = Heartbeat::create([
        'equipo_id' => $connectedEquipment->id,
        'cpu_uso' => 42.5,
        'ram_uso' => 61.25,
        'almacenamiento_uso' => 70,
        'bateria' => 85,
        'temperatura' => 48,
        'fecha_hora' => '2026-10-16 11:50:00',
    ]);
    $connectedHeartbeat->forceFill(['created_at' => '2026-10-16 11:50:00'])->save();
    $olderHeartbeat = Heartbeat::create([
        'equipo_id' => $connectedEquipment->id,
        'cpu_uso' => 99,
        'fecha_hora' => '2026-10-16 11:40:00',
    ]);
    $olderHeartbeat->forceFill(['created_at' => '2026-10-16 11:40:00'])->save();

    $staleHeartbeat = Heartbeat::create([
        'equipo_id' => $staleEquipment->id,
        'cpu_uso' => 15,
        'fecha_hora' => '2026-10-16 11:49:59',
    ]);
    $staleHeartbeat->forceFill(['created_at' => '2026-10-16 11:49:59'])->save();

    $this->actingAs($admin)
        ->get(route('monitoreo.index'))
        ->assertOk()
        ->assertSeeText('EQ-MON-CONECTADO')
        ->assertSeeText('EQ-MON-DESACTUALIZADO')
        ->assertSeeText('EQ-MON-SIN-DATOS')
        ->assertSeeText('Conectado')
        ->assertSeeText('Desconectado')
        ->assertSeeText('Sin datos')
        ->assertSeeText('42.50%');

    $this->actingAs($admin)
        ->get(route('monitoreo.index', ['estado' => 'conectados']))
        ->assertSeeText('EQ-MON-CONECTADO')
        ->assertDontSeeText('EQ-MON-DESACTUALIZADO')
        ->assertDontSeeText('EQ-MON-SIN-DATOS');

    $this->actingAs($admin)
        ->get(route('monitoreo.index', ['estado' => 'desconectados']))
        ->assertSeeText('EQ-MON-DESACTUALIZADO')
        ->assertDontSeeText('EQ-MON-CONECTADO')
        ->assertDontSeeText('EQ-MON-SIN-DATOS');

    $this->actingAs($admin)
        ->get(route('monitoreo.index', ['estado' => 'sin_datos']))
        ->assertSeeText('EQ-MON-SIN-DATOS')
        ->assertDontSeeText('EQ-MON-CONECTADO')
        ->assertDontSeeText('EQ-MON-DESACTUALIZADO');
});

test('staff can filter diagnostics by equipment, type, level, and date', function () {
    $admin = createDiagnosticsMonitoringUser();
    $equipment = createDiagnosticsMonitoringEquipment('EQ-DIAG-001');
    $heartbeat = Heartbeat::create([
        'equipo_id' => $equipment->id,
        'fecha_hora' => '2026-10-15 08:00:00',
    ]);
    Diagnostico::create([
        'equipo_id' => $equipment->id,
        'heartbeat_id' => $heartbeat->id,
        'tipo' => 'Temperatura',
        'nivel' => 'Crítico',
        'descripcion' => 'Temperatura por encima del límite seguro.',
        'fecha_hora' => '2026-10-15 08:01:00',
    ]);
    Diagnostico::create([
        'equipo_id' => $equipment->id,
        'tipo' => 'Almacenamiento',
        'nivel' => 'Informativo',
        'descripcion' => 'Revisión de almacenamiento completada.',
        'fecha_hora' => '2026-10-14 08:01:00',
    ]);

    $this->actingAs($admin)
        ->get(route('diagnosticos.index', [
            'buscar' => 'EQ-DIAG-001',
            'tipo' => 'temperatura',
            'nivel' => 'Crítico',
            'desde' => '2026-10-15',
            'hasta' => '2026-10-16',
        ]))
        ->assertOk()
        ->assertSeeText('Temperatura por encima del límite seguro.')
        ->assertSeeText('Vinculado a heartbeat')
        ->assertDontSeeText('Revisión de almacenamiento completada.');
});

test('dashboard displays heartbeat counts and recent critical diagnostics', function () {
    $this->travelTo(now()->setDate(2026, 10, 16)->setTime(12, 0));
    $admin = createDiagnosticsMonitoringUser();
    $equipment = createDiagnosticsMonitoringEquipment('EQ-DASH-MON');
    $heartbeat = Heartbeat::create([
        'equipo_id' => $equipment->id,
        'fecha_hora' => '2026-10-16 11:55:00',
    ]);
    Diagnostico::create([
        'equipo_id' => $equipment->id,
        'heartbeat_id' => $heartbeat->id,
        'tipo' => 'Hardware',
        'nivel' => 'Crítico',
        'descripcion' => 'Ventilador con funcionamiento irregular.',
        'fecha_hora' => '2026-10-16 11:56:00',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Conectados')
        ->assertSeeText('EQ-DASH-MON')
        ->assertSeeText('Ventilador con funcionamiento irregular.');
});

test('diagnostic date filters reject an end date before the start date', function () {
    $admin = createDiagnosticsMonitoringUser();

    $this->actingAs($admin)
        ->from(route('diagnosticos.index'))
        ->get(route('diagnosticos.index', [
            'desde' => '2026-10-16',
            'hasta' => '2026-10-15',
        ]))
        ->assertSessionHasErrors('hasta');
});

test('students cannot open staff diagnostics or monitoring pages', function () {
    $student = createDiagnosticsMonitoringUser('estudiante');

    $this->actingAs($student)
        ->get(route('monitoreo.index'))
        ->assertForbidden();

    $this->actingAs($student)
        ->get(route('diagnosticos.index'))
        ->assertForbidden();
});

function createDiagnosticsMonitoringUser(string $role = 'administrador'): User
{
    $docente = Docente::create([
        'nombres' => 'Usuario',
        'apellido_paterno' => 'Prueba',
        'apellido_materno' => 'Monitoreo',
        'ci' => fake()->unique()->numerify('########'),
        'cargo' => 'Docente',
        'estado' => 'activo',
    ]);

    return User::create([
        'name' => 'Usuario de monitoreo',
        'username' => fake()->unique()->userName(),
        'password' => 'password',
        'tipo_usuario' => $role,
        'docente_id' => $docente->id,
    ]);
}

function createDiagnosticsMonitoringEquipment(string $codigo): Equipo
{
    return Equipo::create([
        'codigo' => $codigo,
        'tipo_equipo' => 'Tablet',
        'marca' => 'Marca de prueba',
        'modelo' => 'Modelo de prueba',
        'modo_uso' => 'individual',
        'estado' => 'Disponible',
    ]);
}

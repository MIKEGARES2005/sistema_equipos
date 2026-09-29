<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes</title>
</head>
<body>
    <h1>Reportes del sistema</h1>
    <p>
        Resumen general de equipos, asignaciones, incidencias y accesorios.
    </p>
    <hr>
    <h2>Equipos</h2>
    <p>
        <strong>Total:</strong>
        {{ $totalEquipos }}
    </p>
    <p>
        <strong>Disponibles:</strong>
        {{ $equiposDisponibles }}
    </p>
    <p>
        <strong>En uso:</strong>
        {{ $equiposEnUso }}
    </p>
    <p>
        <strong>Otros estados:</strong>
        {{ $equiposOtros }}
    </p>
    <hr>
    <h2>Asignaciones</h2>
    <p>
        <strong>Total:</strong>
        {{ $totalAsignaciones }}
    </p>
    <p>
        <strong>Activas:</strong>
        {{ $asignacionesActivas }}
    </p>
    <p>
        <strong>Finalizadas:</strong>
        {{ $asignacionesFinalizadas }}
    </p>
    <hr>
    <h2>Incidencias</h2>
    <p>
        <strong>Total:</strong>
        {{ $totalIncidencias }}
    </p>
    <p>
        <strong>Pendientes:</strong>
        {{ $incidenciasPendientes }}
    </p>
    <p>
        <strong>Resueltas:</strong>
        {{ $incidenciasResueltas }}
    </p>
    <hr>
    <h2>Accesorios</h2>
    <p>
        <strong>Total registrados:</strong>
        {{ $totalAccesorios }}
    </p>
    <hr>
    <h2>Asignaciones recientes</h2>
    @forelse ($asignacionesRecientes as $asignacion)
        <div>
            <p>
                <strong>Equipo:</strong>
                {{ $asignacion->equipo->codigo }}
            </p>
            <p>
                <strong>Estudiante:</strong>
                {{ $asignacion->estudiante->nombres }}
                {{ $asignacion->estudiante->apellido_paterno }}
            </p>
            <p>
                <strong>Materia:</strong>
                {{ $asignacion->materia->nombre }}
            </p>
            <p>
                <strong>Estado:</strong>
                {{ $asignacion->estado }}
            </p>
        </div>
        <hr>
    @empty
        <p>
            No existen asignaciones registradas.
        </p>
    @endforelse
    <h2>Incidencias recientes</h2>
    @forelse ($incidenciasRecientes as $incidencia)
        <div>
            <p>
                <strong>Equipo:</strong>
                {{ $incidencia->equipo->codigo }}
            </p>
            <p>
                <strong>Tipo:</strong>
                {{ $incidencia->tipo }}
            </p>
            <p>
                <strong>Prioridad:</strong>
                {{ $incidencia->prioridad }}
            </p>
            <p>
                <strong>Estado:</strong>
                {{ $incidencia->estado }}
            </p>
            <p>
                <strong>Descripción:</strong>
                {{ $incidencia->descripcion }}
            </p>
        </div>
        <hr>
    @empty
        <p>
            No existen incidencias registradas.
        </p>
    @endforelse
    <br>
    <a href="{{ route('equipos.index') }}">
        Equipos
    </a>
    |
    <a href="{{ route('asignaciones.index') }}">
        Asignaciones
    </a>
    |
    <a href="{{ route('accesorios.index') }}">
        Accesorios
    </a>
    |
    <a href="{{ route('incidencias.index') }}">
        Incidencias
    </a>
</body>
</html>
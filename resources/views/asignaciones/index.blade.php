<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignaciones</title>
</head>
<body>
    <h1>Gestión de Asignaciones</h1>
    @if (session('success'))
        <p>
            <strong>{{ session('success') }}</strong>
        </p>
    @endif
    <a href="{{ route('asignaciones.create') }}">
        Nueva asignación
    </a>
    <br><br>
    @forelse ($asignaciones as $asignacion)
        <div>
            <h2>
                Equipo: {{ $asignacion->equipo->codigo }}
            </h2>
            <p>
                <strong>Tipo de equipo:</strong>
                {{ $asignacion->equipo->tipo_equipo }}
            </p>
            <p>
                <strong>Modo de uso:</strong>
                {{ $asignacion->equipo->modo_uso }}
            </p>
            <p>
                <strong>Estudiante:</strong>
                {{ $asignacion->estudiante->nombres }}
                {{ $asignacion->estudiante->apellido_paterno }}
                {{ $asignacion->estudiante->apellido_materno }}
            </p>
            <p>
                <strong>Matrícula:</strong>
                {{ $asignacion->estudiante->matricula }}
            </p>
            <p>
                <strong>Semestre:</strong>
                {{ $asignacion->estudiante->semestre }}
            </p>
            <p>
                <strong>Materia:</strong>
                {{ $asignacion->materia->nombre }}
            </p>
            <p>
                <strong>Grupo académico:</strong>
                {{ $asignacion->grupo ?? 'No registrado' }}
            </p>
            <p>
                <strong>Fecha de inicio:</strong>
                {{ $asignacion->fecha_inicio ?? 'No registrada' }}
            </p>
            <p>
                <strong>Fecha de finalización:</strong>
                {{ $asignacion->fecha_fin ?? 'Sin fecha de finalización' }}
            </p>
            <p>
                <strong>Estado:</strong>
                {{ $asignacion->estado }}
            </p>
            @if ($asignacion->estado === 'activa')
            <form
                action="{{ route('asignaciones.finalizar', $asignacion->id) }}"
                method="POST"
            >
            @csrf
            @method('PUT')
                <button type="submit">
                    Registrar devolución
                </button>
            </form>
            @endif
            @if ($asignacion->observaciones)
                <p>
                    <strong>Observaciones:</strong>
                    {{ $asignacion->observaciones }}
                </p>
            @endif
        </div>
        <hr>
    @empty
        <p>No hay asignaciones registradas todavía.</p>
    @endforelse
</body>
</html>
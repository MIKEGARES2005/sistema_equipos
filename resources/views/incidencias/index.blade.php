<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidencias</title>
</head>
<body>
    <h1>Gestión de Incidencias</h1>
    @if (session('success'))
        <p>
            <strong>
                {{ session('success') }}
            </strong>
        </p>
    @endif
    <a href="{{ route('incidencias.create') }}">
        Registrar incidencia
    </a>
    <br><br>
    @forelse ($incidencias as $incidencia)
        <div>
            <h2>
                {{ $incidencia->tipo }}
            </h2>
            <p>
                <strong>Equipo:</strong>
                {{ $incidencia->equipo->codigo }}
            </p>
            @if ($incidencia->asignacion)
                <p>
                    <strong>Asignación:</strong>
                    #{{ $incidencia->asignacion->id }}
                    -
                    {{ $incidencia->asignacion->estudiante->nombres }}
                    {{ $incidencia->asignacion->estudiante->apellido_paterno }}
                </p>
            @else
                <p>
                    <strong>Asignación:</strong>
                    Sin asignación
                </p>
            @endif
            <p>
                <strong>Descripción:</strong>
                {{ $incidencia->descripcion }}
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
                <strong>Fecha de reporte:</strong>
                {{ $incidencia->fecha_reporte }}
            </p>
            @if ($incidencia->fecha_resolucion)
                <p>
                    <strong>Fecha de resolución:</strong>
                    {{ $incidencia->fecha_resolucion }}
                </p>
            @endif
            @if ($incidencia->observaciones)
                <p>
                    <strong>Observaciones:</strong>
                    {{ $incidencia->observaciones }}
                </p>
            @endif
            @if ($incidencia->estado !== 'Resuelta')
                <form
                    action="{{ route('incidencias.resolver', $incidencia->id) }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')
                    <button type="submit">
                        Marcar como resuelta
                    </button>
                </form>
            @endif
            <form
                action="{{ route('incidencias.destroy', $incidencia->id) }}"
                method="POST"
            >
                @csrf
                @method('DELETE')
                <button type="submit">
                    Eliminar
                </button>
            </form>
        </div>
        <hr>
    @empty
        <p>
            No hay incidencias registradas todavía.
        </p>
    @endforelse
</body>
</html>
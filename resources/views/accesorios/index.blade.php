<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesorios</title>
</head>
<body>
    <h1>Gestión de Accesorios</h1>
    @if (session('success'))
        <p>
            <strong>{{ session('success') }}</strong>
        </p>
    @endif
    <a href="{{ route('accesorios.create') }}">
        Registrar accesorio
    </a>
    <br><br>
    @forelse ($accesorios as $accesorio)
        <div>
            <h2>
                {{ $accesorio->nombre }}
            </h2>
            <p>
                <strong>Equipo:</strong>
                {{ $accesorio->equipo->codigo }}
            </p>
            <p>
                <strong>Marca:</strong>
                {{ $accesorio->marca ?? 'No registrada' }}
            </p>
            <p>
                <strong>Modelo:</strong>
                {{ $accesorio->modelo ?? 'No registrado' }}
            </p>
            <p>
                <strong>Número de serie:</strong>
                {{ $accesorio->numero_serie ?? 'No registrado' }}
            </p>
            <p>
                <strong>Cantidad:</strong>
                {{ $accesorio->cantidad }}
            </p>
            <p>
                <strong>Estado:</strong>
                {{ $accesorio->estado }}
            </p>
            @if ($accesorio->observaciones)
                <p>
                    <strong>Observaciones:</strong>
                    {{ $accesorio->observaciones }}
                </p>
            @endif
            <form
                action="{{ route('accesorios.destroy', $accesorio->id) }}"
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
            No hay accesorios registrados todavía.
        </p>
    @endforelse
</body>
</html>
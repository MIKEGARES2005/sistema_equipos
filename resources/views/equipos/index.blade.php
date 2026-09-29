<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Equipos</title>
</head>
<body>
    <h1>Inventario de Equipos</h1>
    @forelse ($equipos as $equipo)
        <div>
            <h2>{{ $equipo->codigo }}</h2>
            <p>Tipo: {{ $equipo->tipo_equipo }}</p>
            <p>Marca: {{ $equipo->marca }}</p>
            <p>Modelo: {{ $equipo->modelo }}</p>
            <p>Estado: {{ $equipo->estado }}</p>
            <a href="{{ route('equipos.show', $equipo->id) }}">
                Ver detalle
            </a>
            <form action="{{ route('equipos.destroy', $equipo->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('¿Está seguro de eliminar este equipo?')">
                    Eliminar
                </button>
            </form>
        </div>
        <hr>
    @empty
        <p>No hay equipos registrados todavía.</p>
    @endforelse
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Equipo</title>
</head>
<body>
    <h1>Detalle del equipo</h1>
    @if ($equipo->imagen_referencial)
        <img
            src="{{ asset('storage/' . $equipo->imagen_referencial) }}"
            alt="Imagen de {{ $equipo->codigo }}"
            width="300"
        >
    @endif
    <h2>{{ $equipo->codigo }}</h2>
    <p><strong>Tipo:</strong> {{ $equipo->tipo_equipo }}</p>
    <p><strong>Marca:</strong> {{ $equipo->marca }}</p>
    <p><strong>Modelo:</strong> {{ $equipo->modelo }}</p>
    <p><strong>Número de serie:</strong> {{ $equipo->numero_serie ?? 'No registrado' }}</p>
    <p><strong>MAC:</strong> {{ $equipo->mac ?? 'No registrada' }}</p>
    <p><strong>Modo de uso:</strong> {{ $equipo->modo_uso }}</p>
    <p><strong>Estado:</strong> {{ $equipo->estado }}</p>
    <p><strong>Última conexión:</strong> {{ $equipo->ultima_conexion ?? 'Sin conexión registrada' }}</p>
    <p><strong>Registrado:</strong> {{ $equipo->created_at }}</p>
    <br>
    <a href="{{ route('equipos.index') }}">Volver al inventario</a>
</body>
</html>
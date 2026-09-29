<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Accesorio</title>
</head>
<body>
    <h1>Registrar accesorio</h1>
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    <form action="{{ route('accesorios.store') }}" method="POST">
        @csrf
        <label for="equipo_id">Equipo:</label><br>
        <select id="equipo_id" name="equipo_id" required>
            <option value="">Seleccione un equipo</option>
            @foreach ($equipos as $equipo)
                <option value="{{ $equipo->id }}">
                    {{ $equipo->codigo }}
                    - {{ $equipo->marca }}
                    {{ $equipo->modelo }}
                </option>
            @endforeach
        </select>
        <br><br>
        <label for="nombre">Nombre del accesorio:</label><br>
        <input
            type="text"
            id="nombre"
            name="nombre"
            placeholder="Ejemplo: Teclado"
            required
        >
        <br><br>
        <label for="marca">Marca:</label><br>
        <input
            type="text"
            id="marca"
            name="marca"
            placeholder="Ejemplo: Logitech"
        >
        <br><br>
        <label for="modelo">Modelo:</label><br>
        <input
            type="text"
            id="modelo"
            name="modelo"
            placeholder="Modelo del accesorio"
        >
        <br><br>
        <label for="numero_serie">Número de serie:</label><br>
        <input
            type="text"
            id="numero_serie"
            name="numero_serie"
            placeholder="Opcional"
        >
        <br><br>
        <label for="cantidad">Cantidad:</label><br>
        <input
            type="number"
            id="cantidad"
            name="cantidad"
            min="1"
            value="1"
            required
        >
        <br><br>
        <label for="estado">Estado:</label><br>
        <select id="estado" name="estado" required>
            <option value="Disponible">Disponible</option>
            <option value="En uso">En uso</option>
            <option value="Dañado">Dañado</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select>
        <br><br>
        <label for="observaciones">Observaciones:</label><br>
        <textarea
            id="observaciones"
            name="observaciones"
            rows="4"
            cols="40"
            placeholder="Observaciones adicionales"
        ></textarea>
        <br><br>
        <button type="submit">
            Registrar accesorio
        </button>
    </form>
    <br>
    <a href="{{ route('accesorios.index') }}">
        Volver a accesorios
    </a>
</body>
</html>
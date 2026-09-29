<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Equipo</title>
</head>
<body>
    <h1>Editar equipo: {{ $equipo->codigo }}</h1>
    <form action="{{ route('equipos.update', $equipo->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <label for="codigo">Código institucional:</label><br>
        <input
            type="text"
            id="codigo"
            name="codigo"
            value="{{ $equipo->codigo }}"
        ><br><br>
        <label for="tipo_equipo">Tipo de equipo:</label><br>
        <select id="tipo_equipo" name="tipo_equipo">
            <option value="PC" {{ $equipo->tipo_equipo == 'PC' ? 'selected' : '' }}>PC</option>
            <option value="Laptop" {{ $equipo->tipo_equipo == 'Laptop' ? 'selected' : '' }}>Laptop</option>
            <option value="Tablet" {{ $equipo->tipo_equipo == 'Tablet' ? 'selected' : '' }}>Tablet</option>
        </select><br><br>
        <label for="marca">Marca:</label><br>
        <input
            type="text"
            id="marca"
            name="marca"
            value="{{ $equipo->marca }}"
        ><br><br>
        <label for="modelo">Modelo:</label><br>
        <input
            type="text"
            id="modelo"
            name="modelo"
            value="{{ $equipo->modelo }}"
        ><br><br>
        <label for="numero_serie">Número de serie:</label><br>
        <input
            type="text"
            id="numero_serie"
            name="numero_serie"
            value="{{ $equipo->numero_serie }}"
        ><br><br>
        <label for="modo_uso">Modo de uso:</label><br>
        <select id="modo_uso" name="modo_uso">
            <option value="individual" {{ $equipo->modo_uso == 'individual' ? 'selected' : '' }}>Individual</option>
            <option value="grupal" {{ $equipo->modo_uso == 'grupal' ? 'selected' : '' }}>Grupal</option>
        </select><br><br>
        <label for="estado">Estado:</label><br>
        <select id="estado" name="estado">
            <option value="Disponible" {{ $equipo->estado == 'Disponible' ? 'selected' : '' }}>Disponible</option>
            <option value="En uso" {{ $equipo->estado == 'En uso' ? 'selected' : '' }}>En uso</option>
            <option value="Mantenimiento" {{ $equipo->estado == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
            <option value="Fuera de servicio" {{ $equipo->estado == 'Fuera de servicio' ? 'selected' : '' }}>Fuera de servicio</option>
        </select><br><br>
        <label for="mac">Dirección MAC:</label><br>
        <input
            type="text"
            id="mac"
            name="mac"
            value="{{ $equipo->mac }}"
        ><br><br>
        <label for="imagen_referencial">Nueva imagen:</label><br>
        <input
            type="file"
            id="imagen_referencial"
            name="imagen_referencial"
        ><br><br>
        <button type="submit">Guardar cambios</button>
    </form>
    <br>
    <a href="{{ route('equipos.show', $equipo->id) }}">
        Cancelar
    </a>
</body>
</html>
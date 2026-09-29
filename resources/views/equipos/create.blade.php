<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Equipo</title>
</head>
<body>
    <h1>Registrar nuevo equipo</h1>
    <form action="{{ route('equipos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="codigo">Código institucional:</label><br>
        <input type="text" id="codigo" name="codigo"><br><br>
        <label for="tipo_equipo">Tipo de equipo:</label><br>
        <select id="tipo_equipo" name="tipo_equipo">
            <option value="">Seleccione un tipo</option>
            <option value="PC">PC</option>
            <option value="Laptop">Laptop</option>
            <option value="Tablet">Tablet</option>
        </select><br><br>
        <label for="marca">Marca:</label><br>
        <input type="text" id="marca" name="marca"><br><br>
        <label for="modelo">Modelo:</label><br>
        <input type="text" id="modelo" name="modelo"><br><br>
        <label for="numero_serie">Número de serie:</label><br>
        <input type="text" id="numero_serie" name="numero_serie"><br><br>
        <label for="modo_uso">Modo de uso:</label><br>
        <select id="modo_uso" name="modo_uso">
            <option value="">Seleccione un modo</option>
            <option value="individual">Individual</option>
            <option value="grupal">Grupal</option>
        </select><br><br>
        <label for="estado">Estado:</label><br>
        <select id="estado" name="estado">
            <option value="">Seleccione un estado</option>
            <option value="Disponible">Disponible</option>
            <option value="En uso">En uso</option>
            <option value="Mantenimiento">Mantenimiento</option>
            <option value="Fuera de servicio">Fuera de servicio</option>
        </select><br><br>
        <label for="mac">Dirección MAC:</label><br>
        <input type="text" id="mac" name="mac"><br><br>
        <label for="imagen_referencial">Imagen referencial:</label><br>
        <input type="file" id="imagen_referencial" name="imagen_referencial"><br><br>
        <button type="submit">Registrar equipo</button>
    </form>
</body>
</html>
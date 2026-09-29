<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Incidencia</title>
</head>
<body>
    <h1>Registrar incidencia</h1>
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    <form action="{{ route('incidencias.store') }}" method="POST">
        @csrf
        <label for="equipo_id">Equipo afectado:</label><br>
        <select id="equipo_id" name="equipo_id" required>
            <option value="">
                Seleccione un equipo
            </option>
            @foreach ($equipos as $equipo)
                <option value="{{ $equipo->id }}">
                    {{ $equipo->codigo }}
                    -
                    {{ $equipo->marca }}
                    {{ $equipo->modelo }}
                </option>
            @endforeach
        </select>
        <br><br>
        <label for="asignacion_id">
            Asignación relacionada (opcional):
        </label><br>
        <select id="asignacion_id" name="asignacion_id">
            <option value="">
                Sin asignación
            </option>
            @foreach ($asignaciones as $asignacion)
                <option value="{{ $asignacion->id }}">
                    {{ $asignacion->equipo->codigo }}
                    -
                    {{ $asignacion->estudiante->nombres }}
                    {{ $asignacion->estudiante->apellido_paterno }}
                </option>
            @endforeach
        </select>
        <br><br>
        <label for="tipo">Tipo de incidencia:</label><br>
        <select id="tipo" name="tipo" required>
            <option value="">
                Seleccione un tipo
            </option>
            <option value="Hardware">
                Hardware
            </option>
            <option value="Software">
                Software
            </option>
            <option value="Red">
                Red
            </option>
            <option value="Bateria">
                Batería
            </option>
            <option value="Pantalla">
                Pantalla
            </option>
            <option value="Periferico">
                Periférico
            </option>
            <option value="Otro">
                Otro
            </option>
        </select>
        <br><br>
        <label for="descripcion">
            Descripción:
        </label><br>
        <textarea
            id="descripcion"
            name="descripcion"
            rows="5"
            cols="45"
            placeholder="Describa el problema..."
            required
        ></textarea>
        <br><br>
        <label for="prioridad">
            Prioridad:
        </label><br>
        <select id="prioridad" name="prioridad" required>
            <option value="Baja">
                Baja
            </option>
            <option value="Media" selected>
                Media
            </option>
            <option value="Alta">
                Alta
            </option>
            <option value="Critica">
                Crítica
            </option>
        </select>
        <br><br>
        <label for="estado">
            Estado:
        </label><br>
        <select id="estado" name="estado" required> 
            <option value="Reportada">
                Reportada
            </option>
            <option value="En revision">
                En revisión
            </option>
        </select>
        <br><br>
        <label for="fecha_reporte">
            Fecha de reporte:
        </label><br>
        <input
            type="datetime-local"
            id="fecha_reporte"
            name="fecha_reporte"
            required
        >
        <br><br>
        <label for="observaciones">
            Observaciones:
        </label><br>
        <textarea
            id="observaciones"
            name="observaciones"
            rows="4"
            cols="45"
            placeholder="Información adicional"
        ></textarea>
        <br><br>
        <button type="submit">
            Registrar incidencia
        </button>
    </form>
    <br>
    <a href="{{ route('incidencias.index') }}">
        Volver a incidencias
    </a>
</body>
</html>
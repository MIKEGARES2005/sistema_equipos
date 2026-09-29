<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Asignación</title>
    @if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    @endif
</head>
<body>
    <h1>Nueva asignación</h1>
    <form action="{{ route('asignaciones.store') }}" method="POST">
        @csrf
        <label for="estudiante_id">Estudiante:</label><br>
        <select id="estudiante_id" name="estudiante_id" onchange="mostrarSemestre()">
            <option value="">Seleccione un estudiante</option>
            @foreach ($estudiantes as $estudiante)
                <option
                    value="{{ $estudiante->id }}"
                    data-semestre="{{ $estudiante->semestre }}"
                >
                    {{ $estudiante->nombres }}
                    {{ $estudiante->apellido_paterno }}
                    {{ $estudiante->apellido_materno }}
                    - {{ $estudiante->matricula }}
                </option>
            @endforeach
        </select>
        <br><br>
        <label>Semestre:</label><br>
        <input
            type="text"
            id="semestre"
            readonly
            placeholder="Se mostrará automáticamente"
        >
        <br><br>
        <label for="materia_id">Materia:</label><br>
        <select id="materia_id" name="materia_id">
            <option value="">Seleccione una materia</option>
            @foreach ($materias as $materia)
                <option value="{{ $materia->id }}">
                    {{ $materia->nombre }}
                    - Semestre {{ $materia->semestre }}
                </option>
            @endforeach
        </select>
        <br><br>
        <label for="equipo_id">Equipo:</label><br>
        <select id="equipo_id" name="equipo_id">
            <option value="">Seleccione un equipo</option>
            @foreach ($equipos as $equipo)
                <option value="{{ $equipo->id }}">
                    {{ $equipo->codigo }}
                    - {{ $equipo->marca }}
                    {{ $equipo->modelo }}
                    - {{ $equipo->modo_uso }}
                </option>
            @endforeach
        </select>
        <br><br>
        <label for="grupo">Grupo (opcional):</label><br>
        <input
            type="text"
            id="grupo"
            name="grupo"
            placeholder="Ejemplo: A"
        >
        <br><br>
        <label for="fecha_inicio">Fecha de inicio:</label><br>
        <input
            type="datetime-local"
            id="fecha_inicio"
            name="fecha_inicio"
        >
        <br><br>
        <label for="fecha_fin">Fecha de finalización (opcional):</label><br>
        <input
            type="datetime-local"
            id="fecha_fin"
            name="fecha_fin"
        >
        <br><br>
        <label for="observaciones">Observaciones (opcional):</label><br>
        <textarea
            id="observaciones"
            name="observaciones"
            rows="4"
            cols="40"
        ></textarea>
        <br><br>
        <button type="submit">
            Crear asignación
        </button>
    </form>
    <br>
    <a href="{{ route('asignaciones.index') }}">
        Volver a asignaciones
    </a>
    <script>
        function mostrarSemestre() {
            const estudiante = document.getElementById('estudiante_id');
            const opcion = estudiante.options[estudiante.selectedIndex];
            const semestre = opcion.getAttribute('data-semestre');
            document.getElementById('semestre').value = semestre || '';
        }
    </script>
</body>
</html>
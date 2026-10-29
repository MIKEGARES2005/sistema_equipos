<x-admin-layout>
    <main class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl space-y-6">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#4959A6]">Gestión de inventario / Asignaciones</p>
                    <h1 class="mt-1 text-3xl font-bold text-slate-800">Nueva asignación</h1>
                    <p class="mt-2 text-sm text-slate-500">Registra el préstamo de un equipo a un estudiante inscrito en la materia.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-semibold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Volver al dashboard</a>
                    <a href="{{ route('asignaciones.index') }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-semibold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Ver asignaciones</a>
                </div>
            </header>

            @if ($errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <p class="font-bold">Revisa los datos del formulario:</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($equipos->isEmpty() || $estudiantes->isEmpty() || $horarios->isEmpty())
                <div role="status" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    No se puede registrar una asignación todavía. Se requiere al menos un equipo disponible, un estudiante activo con inscripción vigente y un horario activo.
                </div>
            @endif

            <form action="{{ route('asignaciones.store') }}" method="POST" class="space-y-6">
                @csrf
                <section class="space-y-5 rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-7">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Personas y actividad académica</h2>
                        <p class="mt-1 text-sm text-slate-500">El estudiante debe tener una inscripción activa en la materia elegida.</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="estudiante_id" class="mb-2 block text-sm font-semibold text-slate-700">Estudiante</label>
                            <select id="estudiante_id" name="estudiante_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecciona un estudiante</option>
                                @foreach ($estudiantes as $estudiante)
                                    <option value="{{ $estudiante->id }}" data-semestre="{{ $estudiante->semestre }}" @selected((string) old('estudiante_id') === (string) $estudiante->id)>
                                        {{ $estudiante->nombres }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }} · {{ $estudiante->matricula }}
                                    </option>
                                @endforeach
                            </select>
                            @error('estudiante_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="semestre" class="mb-2 block text-sm font-semibold text-slate-700">Semestre del estudiante</label>
                            <input id="semestre" type="text" readonly placeholder="Se completa al elegir un estudiante" class="w-full rounded-xl border-slate-200 bg-slate-50 text-sm text-slate-600 shadow-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="horario_id" class="mb-2 block text-sm font-semibold text-slate-700">Materia y horario</label>
                            <select id="horario_id" name="horario_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecciona una materia y horario</option>
                                @foreach ($horarios as $horario)
                                    <option value="{{ $horario->id }}" @selected((string) old('horario_id') === (string) $horario->id)>
                                        {{ $horario->materia_nombre }} · Semestre {{ $horario->semestre }} · {{ $horario->dia_semana }} {{ substr($horario->hora_inicio, 0, 5) }}–{{ substr($horario->hora_fin, 0, 5) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('horario_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="space-y-5 rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-7">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Equipo y periodo</h2>
                        <p class="mt-1 text-sm text-slate-500">Los equipos grupales pueden tener más de una asignación activa.</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="equipo_id" class="mb-2 block text-sm font-semibold text-slate-700">Equipo</label>
                            <select id="equipo_id" name="equipo_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Selecciona un equipo</option>
                                @foreach ($equipos as $equipo)
                                    <option value="{{ $equipo->id }}" @selected((string) old('equipo_id') === (string) $equipo->id)>
                                        {{ $equipo->codigo }} · {{ $equipo->marca }} {{ $equipo->modelo }} · Uso {{ $equipo->modo_uso }}
                                    </option>
                                @endforeach
                            </select>
                            @error('equipo_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="grupo" class="mb-2 block text-sm font-semibold text-slate-700">Grupo (opcional)</label>
                            <input id="grupo" name="grupo" value="{{ old('grupo') }}" maxlength="50" placeholder="Ejemplo: A" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('grupo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="fecha_inicio" class="mb-2 block text-sm font-semibold text-slate-700">Fecha de inicio</label>
                            <input id="fecha_inicio" name="fecha_inicio" type="datetime-local" value="{{ old('fecha_inicio', now()->format('Y-m-d\TH:i')) }}" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('fecha_inicio')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="fecha_fin" class="mb-2 block text-sm font-semibold text-slate-700">Fecha prevista de devolución (opcional)</label>
                            <input id="fecha_fin" name="fecha_fin" type="datetime-local" value="{{ old('fecha_fin') }}" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('fecha_fin')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="observaciones" class="mb-2 block text-sm font-semibold text-slate-700">Observaciones (opcional)</label>
                            <textarea id="observaciones" name="observaciones" rows="4" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('observaciones') }}</textarea>
                            @error('observaciones')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('asignaciones.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancelar</a>
                    <button type="submit" @disabled($equipos->isEmpty() || $estudiantes->isEmpty() || $horarios->isEmpty()) class="inline-flex items-center justify-center rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#4959A6] disabled:cursor-not-allowed disabled:opacity-50">Crear asignación</button>
                </div>
            </form>
        </div>
    </main>
    <script>
        const estudianteSelect = document.getElementById('estudiante_id');
        const semestreInput = document.getElementById('semestre');

        function mostrarSemestre() {
            const opcion = estudianteSelect.options[estudianteSelect.selectedIndex];
            semestreInput.value = opcion.dataset.semestre || '';
        }

        estudianteSelect.addEventListener('change', mostrarSemestre);
        mostrarSemestre();
    </script>
</x-admin-layout>

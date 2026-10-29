<x-admin-layout>
    <x-admin-shell>
    <div class="min-h-screen bg-slate-50">
        <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-7">
                <a href="{{ $semestre ? route('gestion-academica.semestre', ['semestre' => $semestre, 'gestion' => $gestion]) : route('gestion-academica.index', ['gestion' => $gestion]) }}" class="text-sm font-semibold text-[#4959A6] hover:underline">← Volver a Gestión académica</a>
                <h1 class="mt-3 text-3xl font-bold text-slate-800">{{ $horario->exists ? 'Editar horario' : 'Crear horario' }}</h1>
                <p class="mt-2 text-sm text-slate-500">Asigna una materia, un docente y su bloque de clases.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ $horario->exists ? route('horarios.update', $horario) : route('horarios.store') }}"
                  class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                @csrf
                @if ($horario->exists) @method('PUT') @endif
                <input type="hidden" name="gestion" value="{{ $gestion }}">
                <div>
                    <label for="materia_id" class="mb-2 block text-sm font-semibold text-slate-700">Materia</label>
                    <select id="materia_id" name="materia_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecciona una materia</option>
                        @foreach ($materias as $materia)
                            <option value="{{ $materia->id }}" @selected(old('materia_id', $horario->materia_id) == $materia->id)>
                                {{ $materia->sigla ? $materia->sigla.' · ' : '' }}{{ $materia->nombre }} · {{ $materia->semestre }}{{ strtolower($materia->estado) === 'activo' || strtolower($materia->estado) === 'activa' ? '' : ' (inactiva)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('materia_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="docente_id" class="mb-2 block text-sm font-semibold text-slate-700">Docente</label>
                    <select id="docente_id" name="docente_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecciona un docente</option>
                        @foreach ($docentes as $docente)
                            <option value="{{ $docente->id }}" @selected(old('docente_id', $horario->docente_id) == $docente->id)>
                                {{ $docente->nombres }} {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}
                            </option>
                        @endforeach
                    </select>
                    @error('docente_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="dia_semana" class="mb-2 block text-sm font-semibold text-slate-700">Día de la semana</label>
                    <select id="dia_semana" name="dia_semana" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecciona un día</option>
                        @foreach ($diasSemana as $dia)
                            <option value="{{ $dia }}" @selected(old('dia_semana', $horario->dia_semana) === $dia)>{{ $dia }}</option>
                        @endforeach
                    </select>
                    @error('dia_semana')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="hora_inicio" class="mb-2 block text-sm font-semibold text-slate-700">Hora de inicio</label>
                        <input id="hora_inicio" name="hora_inicio" type="time" required
                               value="{{ old('hora_inicio', $horario->hora_inicio ? substr($horario->hora_inicio, 0, 5) : '') }}"
                               class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('hora_inicio')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="hora_fin" class="mb-2 block text-sm font-semibold text-slate-700">Hora de finalización</label>
                        <input id="hora_fin" name="hora_fin" type="time" required
                               value="{{ old('hora_fin', $horario->hora_fin ? substr($horario->hora_fin, 0, 5) : '') }}"
                               class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('hora_fin')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="aula" class="mb-2 block text-sm font-semibold text-slate-700">Aula (opcional)</label>
                    <input id="aula" name="aula" value="{{ old('aula', $horario->aula) }}" maxlength="100"
                           class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('aula')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ $semestre ? route('gestion-academica.semestre', ['semestre' => $semestre, 'gestion' => $gestion]) : route('gestion-academica.index', ['gestion' => $gestion]) }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white hover:bg-[#4959A6]">
                        {{ $horario->exists ? 'Guardar cambios' : 'Crear horario' }}
                    </button>
                </div>
            </form>
        </main>
    </div>
    </x-admin-shell>
</x-admin-layout>

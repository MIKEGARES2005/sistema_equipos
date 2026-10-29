<x-admin-layout>
    <x-admin-shell>
    <div class="min-h-screen bg-slate-50">
        <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-7">
                <a href="{{ $semestre ? route('gestion-academica.semestre', ['semestre' => $semestre, 'gestion' => $inscripcion->gestion]) : route('gestion-academica.index', ['gestion' => $inscripcion->gestion]) }}" class="text-sm font-semibold text-[#4959A6] hover:underline">← Volver a Gestión académica</a>
                <h1 class="mt-3 text-3xl font-bold text-slate-800">{{ $inscripcion->exists ? 'Editar inscripción' : 'Registrar inscripción' }}</h1>
                <p class="mt-2 text-sm text-slate-500">Relaciona al estudiante con una materia.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ $inscripcion->exists ? route('inscripciones.update', $inscripcion) : route('inscripciones.store') }}"
                  class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                @csrf
                @if ($inscripcion->exists) @method('PUT') @endif
                <div>
                    <label for="gestion" class="mb-2 block text-sm font-semibold text-slate-700">Gestión académica</label>
                    <input id="gestion" name="gestion" value="{{ old('gestion', $inscripcion->gestion) }}" required
                           pattern="[0-9]{4}-(I|II)" placeholder="Ej. 2026-II"
                           class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-slate-500">Formato: año y periodo, por ejemplo 2026-I o 2026-II.</p>
                    @error('gestion')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="estudiante_id" class="mb-2 block text-sm font-semibold text-slate-700">Estudiante</label>
                    <select id="estudiante_id" name="estudiante_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecciona un estudiante</option>
                        @foreach ($estudiantes as $estudiante)
                            <option value="{{ $estudiante->id }}" @selected(old('estudiante_id', $inscripcion->estudiante_id) == $estudiante->id)>
                                {{ $estudiante->nombres }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }} · {{ $estudiante->matricula }}
                            </option>
                        @endforeach
                    </select>
                    @error('estudiante_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="materia_id" class="mb-2 block text-sm font-semibold text-slate-700">Materia</label>
                    <select id="materia_id" name="materia_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecciona una materia</option>
                        @foreach ($materias as $materia)
                            <option value="{{ $materia->id }}" @selected(old('materia_id', $inscripcion->materia_id) == $materia->id)>
                                {{ $materia->sigla ? $materia->sigla.' · ' : '' }}{{ $materia->nombre }} · {{ $materia->semestre }}{{ strtolower($materia->estado) === 'activo' || strtolower($materia->estado) === 'activa' ? '' : ' (inactiva)' }}
                            </option>
                        @endforeach
                    </select>
                    @error('materia_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tipo" class="mb-2 block text-sm font-semibold text-slate-700">Tipo de inscripción</label>
                    <select id="tipo" name="tipo" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecciona un tipo</option>
                        @foreach (['Normal', 'Arrastre'] as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo', $inscripcion->tipo) === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                    @error('tipo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                @if ($inscripcion->exists)
                    <div>
                        <label for="estado" class="mb-2 block text-sm font-semibold text-slate-700">Estado académico</label>
                        <select id="estado" name="estado" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (['Cursando', 'Aprobado', 'Reprobado', 'Inactivo'] as $estado)
                                <option value="{{ $estado }}" @selected(old('estado', $inscripcion->estado) === $estado)>{{ $estado }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Para aprobar o reprobar, primero debe existir el resultado reportado por el docente.</p>
                        @error('estado')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endif
                <div>
                    <label for="fecha_inscripcion" class="mb-2 block text-sm font-semibold text-slate-700">Fecha de inscripción</label>
                    <input id="fecha_inscripcion" name="fecha_inscripcion" type="date"
                           value="{{ old('fecha_inscripcion', $inscripcion->fecha_inscripcion?->format('Y-m-d')) }}"
                           required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('fecha_inscripcion')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ $semestre ? route('gestion-academica.semestre', ['semestre' => $semestre, 'gestion' => $inscripcion->gestion]) : route('gestion-academica.index', ['gestion' => $inscripcion->gestion]) }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white hover:bg-[#4959A6]">
                        {{ $inscripcion->exists ? 'Guardar cambios' : 'Registrar inscripción' }}
                    </button>
                </div>
            </form>
        </main>
    </div>
    </x-admin-shell>
</x-admin-layout>

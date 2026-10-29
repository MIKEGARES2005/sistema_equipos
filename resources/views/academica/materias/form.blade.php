<x-admin-layout>
    <x-admin-shell>
    <div class="min-h-screen bg-slate-50">
        <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-7">
                <a href="{{ $semestreSeleccionado ? route('gestion-academica.semestre', ['semestre' => $semestreSeleccionado, 'gestion' => $gestion]) : route('gestion-academica.index', ['gestion' => $gestion]) }}" class="text-sm font-semibold text-[#4959A6] hover:underline">← Volver a Gestión académica</a>
                <h1 class="mt-3 text-3xl font-bold text-slate-800">{{ $materia->exists ? 'Editar materia' : 'Crear materia' }}</h1>
                <p class="mt-2 text-sm text-slate-500">Completa los datos académicos de la materia.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ $materia->exists ? route('materias.update', $materia) : route('materias.store') }}"
                  class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                @csrf
                @if ($materia->exists) @method('PUT') @endif
                <input type="hidden" name="gestion" value="{{ old('gestion', $gestion) }}">
                <div>
                    <label for="nombre" class="mb-2 block text-sm font-semibold text-slate-700">Nombre de la materia</label>
                    <input id="nombre" name="nombre" value="{{ old('nombre', $materia->nombre) }}" required maxlength="150"
                           class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('nombre')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="sigla" class="mb-2 block text-sm font-semibold text-slate-700">Sigla</label>
                    <input id="sigla" name="sigla" value="{{ old('sigla', $materia->sigla) }}" required maxlength="20" placeholder="Ej. PROG-I"
                           class="w-full rounded-xl border-slate-300 text-sm uppercase shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-slate-500">Identificador corto y único de la materia.</p>
                    @error('sigla')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="semestre" class="mb-2 block text-sm font-semibold text-slate-700">Semestre</label>
                    <select id="semestre" name="semestre" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Selecciona un semestre</option>
                        @foreach ($semestres as $opcionSemestre)
                            <option value="{{ $opcionSemestre }}" @selected(old('semestre', $materia->semestre ?: $semestreSeleccionado) === $opcionSemestre)>{{ $opcionSemestre }}</option>
                        @endforeach
                    </select>
                    @error('semestre')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ $semestreSeleccionado ? route('gestion-academica.semestre', ['semestre' => $semestreSeleccionado, 'gestion' => $gestion]) : route('gestion-academica.index', ['gestion' => $gestion]) }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white hover:bg-[#4959A6]">
                        {{ $materia->exists ? 'Guardar cambios' : 'Crear materia' }}
                    </button>
                </div>
            </form>
        </main>
    </div>
    </x-admin-shell>
</x-admin-layout>

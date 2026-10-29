<x-docente-layout>
    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-[#4959A6]">Panel docente / Académico</p>
            <h2 class="mt-1 text-3xl font-bold text-slate-800">Panel Estudiantil</h2>
            <p class="mt-2 text-sm text-slate-500">Estudiantes cursando en tus materias durante la gestión {{ $gestion }}.</p>
        </div>
        <span class="inline-flex w-fit rounded-xl bg-indigo-100 px-4 py-2 text-sm font-bold text-[#272F7A]">Gestión {{ $gestion }}</span>
    </section>

    <section aria-label="Resumen de estudiantes" class="grid gap-4 sm:grid-cols-2">
        <article class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Estudiantes en tus materias</p>
            <p class="mt-3 text-3xl font-bold text-[#272F7A]">{{ $totalEstudiantes }}</p>
        </article>
        <article class="rounded-2xl border border-blue-100 bg-gradient-to-br from-white to-blue-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Inscripciones cursando</p>
            <p class="mt-3 text-3xl font-bold text-blue-700">{{ $totalInscripciones }}</p>
        </article>
    </section>

    <section class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-6">
        <form method="GET" action="{{ route('panel-docente.estudiantes') }}" class="grid gap-4 sm:grid-cols-[1fr_1fr_auto_auto] sm:items-end">
            <div>
                <label for="buscar" class="mb-2 block text-sm font-semibold text-slate-700">Buscar estudiante o materia</label>
                <input id="buscar" name="buscar" type="search" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Nombre, matrícula, sigla o materia" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="materia_id" class="mb-2 block text-sm font-semibold text-slate-700">Materia</label>
                <select id="materia_id" name="materia_id" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todas mis materias</option>
                    @foreach ($materias as $materia)
                        <option value="{{ $materia->id }}" @selected((string) ($filtros['materia_id'] ?? '') === (string) $materia->id)>{{ $materia->sigla ? $materia->sigla.' · ' : '' }}{{ $materia->nombre }} · {{ $materia->semestre }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#272F7A] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#4959A6]">Filtrar</button>
            @if (($filtros['buscar'] ?? '') !== '' || ($filtros['materia_id'] ?? null))
                <a href="{{ route('panel-docente.estudiantes') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Limpiar</a>
            @endif
        </form>
    </section>

    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
        <div class="border-b border-indigo-50 bg-gradient-to-r from-indigo-50 to-blue-50/60 px-5 py-4">
            <h3 class="font-bold text-slate-800">Estudiantes inscritos</h3>
            <p class="mt-1 text-xs text-slate-500">Solo materias activas asignadas a tu perfil y estudiantes con inscripción cursando en la gestión vigente.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Estudiante</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Matrícula</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Semestre</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Materia</th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Inscripción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($inscripciones as $inscripcion)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-800">{{ trim(($inscripcion->estudiante?->nombres ?? '').' '.($inscripcion->estudiante?->apellido_paterno ?? '').' '.($inscripcion->estudiante?->apellido_materno ?? '')) ?: 'Estudiante no disponible' }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $inscripcion->estudiante?->matricula ?? 'No registrada' }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $inscripcion->estudiante?->semestre ?? 'No registrado' }}</td>
                            <td class="px-5 py-4 text-sm">
                                <p class="font-semibold text-slate-800">{{ $inscripcion->materia?->sigla ? $inscripcion->materia->sigla.' · ' : '' }}{{ $inscripcion->materia?->nombre ?? 'Materia no disponible' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $inscripcion->materia?->semestre ?? '' }} · {{ $inscripcion->gestion }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4"><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $inscripcion->estado }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="font-semibold text-slate-700">{{ ($filtros['buscar'] ?? '') !== '' || ($filtros['materia_id'] ?? null) ? 'No encontramos estudiantes con esos filtros' : 'Aún no hay estudiantes cursando en tus materias' }}</p>
                                <p class="mt-2 text-sm text-slate-500">La lista corresponde a la gestión vigente y se actualizará con las inscripciones académicas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $inscripciones->links() }}</div>
    </section>
</x-docente-layout>

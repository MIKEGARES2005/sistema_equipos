<x-admin-layout>
    <x-admin-shell>
    <div class="min-h-screen bg-slate-50">
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#4959A6]">Gestión académica</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-800">Horarios</h1>
                    <p class="mt-2 text-sm text-slate-500">Consulta la programación de materias y docentes.</p>
                </div>
                @if (auth()->user()->isAdministrador())
                    <a href="{{ route('horarios.create') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#4959A6]">
                        Crear horario
                    </a>
                @endif
            </header>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Materia</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Semestre</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Docente</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Día y hora</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Aula</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Estado</th>
                                @if (auth()->user()->isAdministrador())
                                    <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($horarios as $horario)
                                @php($horarioActivo = in_array(strtolower($horario->estado), ['activo', 'activa'], true))
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 font-semibold text-slate-800">{{ $horario->materia?->nombre ?? 'Materia no disponible' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $horario->materia?->semestre ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">
                                        {{ trim(($horario->docente?->nombres ?? '').' '.($horario->docente?->apellido_paterno ?? '')) ?: 'Docente no disponible' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                        {{ $horario->dia_semana }} · {{ substr($horario->hora_inicio, 0, 5) }}–{{ substr($horario->hora_fin, 0, 5) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $horario->aula ?: 'Sin aula' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $horarioActivo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $horarioActivo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    @if (auth()->user()->isAdministrador())
                                        <td class="px-6 py-4">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('horarios.edit', $horario) }}"
                                                   class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-bold text-[#272F7A] hover:bg-indigo-100">Editar</a>
                                                <form method="POST" action="{{ route('horarios.status', $horario) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="estado" value="{{ $horarioActivo ? 'inactivo' : 'activo' }}">
                                                    <button type="submit" class="rounded-lg px-3 py-2 text-xs font-bold {{ $horarioActivo ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                        {{ $horarioActivo ? 'Inhabilitar' : 'Habilitar' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->isAdministrador() ? 7 : 6 }}" class="px-6 py-12 text-center text-sm text-slate-500">No hay horarios asignados para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-6 py-4">{{ $horarios->links() }}</div>
            </section>
        </main>
    </div>
    </x-admin-shell>
</x-admin-layout>

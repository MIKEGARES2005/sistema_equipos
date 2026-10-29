<x-admin-layout>
    <x-admin-shell>
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-500">Administración / Académico</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Seguimiento estudiantil</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Consulta la trayectoria, el rendimiento y los eventos documentados de cada estudiante.</p>
                </div>
                <span class="inline-flex w-fit items-center rounded-xl bg-indigo-50 px-4 py-2.5 text-sm font-bold text-[#272F7A] ring-1 ring-indigo-100">Gestión vigente: {{ $gestionVigente }}</span>
            </header>

            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="grid gap-4 sm:grid-cols-3">
                @foreach ([['label' => 'Estudiantes activos', 'value' => $totalActivos, 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50'], ['label' => 'Con seguimiento', 'value' => $totalConSeguimiento, 'color' => 'text-indigo-700', 'bg' => 'bg-indigo-50'], ['label' => 'Pendientes de seguimiento', 'value' => $totalPendientes, 'color' => 'text-amber-700', 'bg' => 'bg-amber-50']] as $estadistica)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <span class="inline-flex rounded-xl {{ $estadistica['bg'] }} px-3 py-2 text-xs font-black uppercase tracking-wide {{ $estadistica['color'] }}">{{ $estadistica['label'] }}</span>
                        <p class="mt-4 text-3xl font-black {{ $estadistica['color'] }}">{{ $estadistica['value'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="rounded-2xl border border-indigo-100 bg-white p-4 shadow-sm sm:p-5">
                <form method="GET" action="{{ route('seguimiento-estudiantil.index') }}" class="grid gap-3 sm:grid-cols-[1fr_12rem_auto] sm:items-end">
                    <div>
                        <label for="buscar" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Buscar estudiante</label>
                        <input id="buscar" name="buscar" value="{{ $buscar }}" placeholder="Nombre, apellido, CI o matrícula" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="semestre" class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Semestre</label>
                        <select id="semestre" name="semestre" class="w-full rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            @foreach (['1' => 'Primero', '2' => 'Segundo', '3' => 'Tercero', '4' => 'Cuarto'] as $valor => $nombre)
                                <option value="{{ $valor }}" @selected((string) $semestre === $valor)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#272F7A] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#4959A6]">Filtrar</button>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-indigo-50/70 to-white px-5 py-4">
                    <h2 class="text-sm font-black text-slate-800">Estudiantes en seguimiento</h2>
                    <p class="mt-1 text-xs text-slate-500">Selecciona un estudiante para consultar sus materias, notas, docentes y eventos.</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($estudiantes as $estudiante)
                        <a href="{{ route('seguimiento-estudiantil.show', $estudiante) }}" class="group flex flex-col gap-4 px-5 py-5 transition hover:bg-indigo-50/60 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-100 font-black text-indigo-700">{{ strtoupper(substr($estudiante->nombres, 0, 1).substr($estudiante->apellido_paterno, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate font-black text-slate-800">{{ $estudiante->nombreCompleto() }}</p>
                                    <p class="mt-1 text-xs text-slate-500">CI {{ $estudiante->ci }} · Matrícula {{ $estudiante->matricula ?: 'Sin matrícula' }} · {{ $estudiante->semestre }}° semestre</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 sm:pl-0">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $estudiante->estado === 'activo' ? 'bg-emerald-50 text-emerald-700' : ($estudiante->estado === 'congelado' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ ucfirst($estudiante->estado) }}</span>
                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ $estudiante->historial_seguimiento_count }} evento(s)</span>
                                <span class="text-lg font-black text-indigo-600 transition group-hover:translate-x-1">→</span>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-14 text-center text-sm text-slate-500">No se encontraron estudiantes con esos filtros.</div>
                    @endforelse
                </div>
                <div class="border-t border-slate-100 px-5 py-4">{{ $estudiantes->links() }}</div>
            </section>
        </main>
    </x-admin-shell>
</x-admin-layout>

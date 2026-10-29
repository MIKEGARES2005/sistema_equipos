<x-admin-layout>
    <main class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70">
        <div class="mx-auto max-w-7xl space-y-7 px-4 py-8 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#4959A6]">Actividad y monitoreo</p>
                    <h1 class="mt-1 text-3xl font-bold text-slate-800">Diagnósticos</h1>
                    <p class="mt-2 max-w-3xl text-sm text-slate-500">Hallazgos reportados por las computadoras y tablets; esta vista no modifica ni genera diagnósticos.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-semibold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Volver al dashboard</a>
            </header>

            @if ($errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <section class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-6">
                <form method="GET" action="{{ route('diagnosticos.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label for="buscar" class="mb-2 block text-sm font-semibold text-slate-700">Buscar</label>
                        <input id="buscar" name="buscar" type="search" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Código de equipo, tipo, nivel o descripción" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="tipo" class="mb-2 block text-sm font-semibold text-slate-700">Tipo de diagnóstico</label>
                        <select id="tipo" name="tipo" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos los tipos</option>
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo }}" @selected(($filtros['tipo'] ?? '') === $tipo)>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="nivel" class="mb-2 block text-sm font-semibold text-slate-700">Nivel</label>
                        <select id="nivel" name="nivel" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos los niveles</option>
                            @foreach ($niveles as $nivel)
                                <option value="{{ $nivel }}" @selected(($filtros['nivel'] ?? '') === $nivel)>{{ $nivel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="desde" class="mb-2 block text-sm font-semibold text-slate-700">Desde</label>
                            <input id="desde" name="desde" type="date" value="{{ $filtros['desde'] ?? '' }}" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="hasta" class="mb-2 block text-sm font-semibold text-slate-700">Hasta</label>
                            <input id="hasta" name="hasta" type="date" value="{{ $filtros['hasta'] ?? '' }}" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="flex flex-wrap items-end gap-3 sm:col-span-2 lg:col-span-3">
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#272F7A] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#4959A6]">Aplicar filtros</button>
                        @if (collect($filtros)->filter(fn ($valor) => $valor !== null && $valor !== '')->isNotEmpty())
                            <a href="{{ route('diagnosticos.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Limpiar</a>
                        @endif
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-indigo-50 bg-gradient-to-r from-indigo-50 to-blue-50/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800">Historial de diagnósticos</h2>
                        <p class="mt-1 text-xs text-slate-500">Los registros más recientes aparecen primero.</p>
                    </div>
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-[#272F7A]">{{ $diagnosticos->total() }} registros</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($diagnosticos as $diagnostico)
                        @php
                            $nivelNormalizado = strtolower($diagnostico->nivel);
                            $nivelEstilo = in_array($nivelNormalizado, ['critico', 'crítico'], true)
                                ? 'bg-red-50 text-red-700'
                                : (in_array($nivelNormalizado, ['alto', 'advertencia', 'warning'], true)
                                    ? 'bg-amber-50 text-amber-800'
                                    : 'bg-slate-100 text-slate-700');
                        @endphp
                        <article class="px-5 py-5 sm:px-6">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ $diagnostico->equipo ? route('equipos.show', $diagnostico->equipo->id) : '#' }}" class="font-bold text-[#272F7A] hover:underline">{{ $diagnostico->equipo?->codigo ?? 'Equipo no disponible' }}</a>
                                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-[#4959A6]">{{ $diagnostico->tipo }}</span>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $nivelEstilo }}">{{ $diagnostico->nivel }}</span>
                                    </div>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $diagnostico->descripcion }}</p>
                                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                                        <span>Registrado: {{ $diagnostico->fecha_hora?->format('d/m/Y H:i:s') ?? 'Sin fecha' }}</span>
                                        @if ($diagnostico->heartbeat)
                                            <span>Vinculado a heartbeat #{{ $diagnostico->heartbeat->id }}</span>
                                            @if ($diagnostico->heartbeat->cpu_uso !== null)
                                                <span>CPU: {{ $diagnostico->heartbeat->cpu_uso }}%</span>
                                            @endif
                                            @if ($diagnostico->heartbeat->ram_uso !== null)
                                                <span>RAM: {{ $diagnostico->heartbeat->ram_uso }}%</span>
                                            @endif
                                        @else
                                            <span>Sin heartbeat asociado</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="px-5 py-14 text-center">
                            <p class="text-lg font-bold text-slate-700">{{ collect($filtros)->filter(fn ($valor) => $valor !== null && $valor !== '')->isNotEmpty() ? 'No hay diagnósticos con esos filtros' : 'Aún no hay diagnósticos registrados' }}</p>
                            <p class="mt-2 text-sm text-slate-500">Cuando la aplicación de las computadoras y tablets envíe resultados, aparecerán aquí.</p>
                        </div>
                    @endforelse
                </div>
                <div class="border-t border-slate-100 px-5 py-4">{{ $diagnosticos->links() }}</div>
            </section>
        </div>
    </main>
</x-admin-layout>

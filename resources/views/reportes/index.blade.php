<x-admin-layout>
    <main class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70">
        <div class="mx-auto max-w-7xl space-y-7 px-4 py-8 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#4959A6]">Administración / Análisis</p>
                    <h1 class="mt-1 text-3xl font-bold text-slate-800">Reportes del sistema</h1>
                    <p class="mt-2 text-sm text-slate-500">Resumen del inventario y actividad de asignaciones e incidencias.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-semibold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Volver al dashboard</a>
            </header>

            <section class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4">
                    <h2 class="font-bold text-slate-800">Filtrar actividad por fechas</h2>
                    <p class="mt-1 text-sm text-slate-500">El periodo afecta las asignaciones y las incidencias. Los datos de equipos y accesorios muestran el inventario actual.</p>
                </div>
                <form method="GET" action="{{ route('reportes.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto_auto] lg:items-end">
                    <div>
                        <label for="desde" class="mb-2 block text-sm font-semibold text-slate-700">Desde</label>
                        <input id="desde" name="desde" type="date" value="{{ $filtros['desde'] ?? '' }}" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="hasta" class="mb-2 block text-sm font-semibold text-slate-700">Hasta</label>
                        <input id="hasta" name="hasta" type="date" value="{{ $filtros['hasta'] ?? '' }}" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#272F7A] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#4959A6]">Aplicar periodo</button>
                    @if (($filtros['desde'] ?? null) || ($filtros['hasta'] ?? null))
                        <a href="{{ route('reportes.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Limpiar</a>
                    @endif
                </form>
                @if ($errors->any())
                    <div role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
            </section>

            <section aria-label="Resumen del inventario" class="space-y-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Inventario actual</h2>
                    <p class="mt-1 text-sm text-slate-500">Estado de los equipos y accesorios registrados.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Total de equipos</p>
                        <p class="mt-3 text-3xl font-bold text-[#272F7A]">{{ $estadisticas['totalEquipos'] }}</p>
                        <a href="{{ route('equipos.index') }}" class="mt-3 inline-flex text-sm font-semibold text-[#4959A6] hover:underline">Ver equipos →</a>
                    </article>
                    <article class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50 p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Disponibles</p>
                        <p class="mt-3 text-3xl font-bold text-emerald-700">{{ $estadisticas['equiposDisponibles'] }}</p>
                        <p class="mt-3 text-sm text-slate-500">Listos para utilizarse</p>
                    </article>
                    <article class="rounded-2xl border border-blue-100 bg-gradient-to-br from-white to-blue-50 p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">En uso</p>
                        <p class="mt-3 text-3xl font-bold text-blue-700">{{ $estadisticas['equiposEnUso'] }}</p>
                        <p class="mt-3 text-sm text-slate-500">Actualmente asignados</p>
                    </article>
                    <article class="rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50 p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Otros estados</p>
                        <p class="mt-3 text-3xl font-bold text-amber-700">{{ $estadisticas['equiposOtros'] }}</p>
                        <p class="mt-3 text-sm text-slate-500">Mantenimiento u otros</p>
                    </article>
                    <article class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm sm:col-span-2 xl:col-span-1">
                        <p class="text-sm font-medium text-slate-500">Accesorios registrados</p>
                        <p class="mt-3 text-3xl font-bold text-violet-700">{{ $estadisticas['totalAccesorios'] }}</p>
                        <a href="{{ route('accesorios.index') }}" class="mt-3 inline-flex text-sm font-semibold text-[#4959A6] hover:underline">Ver accesorios →</a>
                    </article>
                </div>
            </section>

            <section aria-label="Resumen de actividad" class="space-y-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Actividad del periodo</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        @if (($filtros['desde'] ?? null) || ($filtros['hasta'] ?? null))
                            Resultados según las fechas seleccionadas.
                        @else
                            Resumen de toda la actividad registrada.
                        @endif
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-2">
                    <article class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-white to-indigo-50 p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-slate-500">Asignaciones</p>
                            <span class="rounded-xl bg-indigo-100 px-3 py-1 text-xs font-bold text-[#272F7A]">Total {{ $estadisticas['totalAsignaciones'] }}</span>
                        </div>
                        <div class="mt-4 flex gap-6">
                            <p class="text-sm text-slate-600">Activas <span class="ml-1 font-bold text-emerald-700">{{ $estadisticas['asignacionesActivas'] }}</span></p>
                            <p class="text-sm text-slate-600">Finalizadas <span class="ml-1 font-bold text-slate-700">{{ $estadisticas['asignacionesFinalizadas'] }}</span></p>
                        </div>
                        <a href="{{ route('asignaciones.index') }}" class="mt-4 inline-flex text-sm font-semibold text-[#4959A6] hover:underline">Ver asignaciones →</a>
                    </article>
                    <article class="rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50 p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-slate-500">Incidencias</p>
                            <span class="rounded-xl bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Total {{ $estadisticas['totalIncidencias'] }}</span>
                        </div>
                        <div class="mt-4 flex gap-6">
                            <p class="text-sm text-slate-600">Pendientes <span class="ml-1 font-bold text-amber-700">{{ $estadisticas['incidenciasPendientes'] }}</span></p>
                            <p class="text-sm text-slate-600">Resueltas <span class="ml-1 font-bold text-emerald-700">{{ $estadisticas['incidenciasResueltas'] }}</span></p>
                        </div>
                        <a href="{{ route('incidencias.index') }}" class="mt-4 inline-flex text-sm font-semibold text-[#4959A6] hover:underline">Ver incidencias →</a>
                    </article>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <article class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-indigo-50 bg-gradient-to-r from-indigo-50 to-blue-50/60 px-5 py-4">
                        <div>
                            <h2 class="font-bold text-slate-800">Asignaciones recientes</h2>
                            <p class="mt-1 text-xs text-slate-500">Hasta cinco registros del periodo</p>
                        </div>
                        <a href="{{ route('asignaciones.index') }}" class="text-sm font-semibold text-[#4959A6] hover:underline">Ver todas</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($asignacionesRecientes as $asignacion)
                            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800">{{ $asignacion->equipo?->codigo ?? 'Equipo no disponible' }} <span class="font-normal text-slate-500">· {{ $asignacion->equipo?->marca }} {{ $asignacion->equipo?->modelo }}</span></p>
                                    <p class="mt-1 text-sm text-slate-600">{{ $asignacion->estudiante?->nombres }} {{ $asignacion->estudiante?->apellido_paterno }} {{ $asignacion->estudiante?->apellido_materno }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $asignacion->materia?->sigla ? $asignacion->materia->sigla.' · ' : '' }}{{ $asignacion->materia?->nombre ?? 'Materia no disponible' }} · {{ $asignacion->fecha_inicio?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ strtolower($asignacion->estado) === 'activa' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst(strtolower($asignacion->estado)) }}</span>
                            </div>
                        @empty
                            <p class="px-5 py-10 text-center text-sm text-slate-500">No hay asignaciones en este periodo.</p>
                        @endforelse
                    </div>
                </article>

                <article class="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-amber-50 bg-gradient-to-r from-amber-50 to-orange-50/60 px-5 py-4">
                        <div>
                            <h2 class="font-bold text-slate-800">Incidencias recientes</h2>
                            <p class="mt-1 text-xs text-slate-500">Hasta cinco reportes del periodo</p>
                        </div>
                        <a href="{{ route('incidencias.index') }}" class="text-sm font-semibold text-[#4959A6] hover:underline">Ver todas</a>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($incidenciasRecientes as $incidencia)
                            <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-4">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800">{{ $incidencia->equipo?->codigo ?? 'Equipo no disponible' }} <span class="font-normal text-slate-500">· {{ $incidencia->tipo }}</span></p>
                                    <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $incidencia->descripcion }}</p>
                                    <p class="mt-1 text-xs text-slate-400">Reportada: {{ $incidencia->fecha_reporte?->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">{{ $incidencia->prioridad === 'Critica' ? 'Crítica' : $incidencia->prioridad }}</span>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ strtolower($incidencia->estado) === 'resuelta' ? 'bg-emerald-50 text-emerald-700' : 'bg-orange-50 text-orange-700' }}">{{ $incidencia->estado }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="px-5 py-10 text-center text-sm text-slate-500">No hay incidencias en este periodo.</p>
                        @endforelse
                    </div>
                </article>
            </section>
        </div>
    </main>
</x-admin-layout>

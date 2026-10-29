<x-admin-layout>
    <main class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70">
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#4959A6]">Gestión de inventario / Asignaciones</p>
                    <h1 class="mt-1 text-3xl font-bold text-slate-800">Asignaciones</h1>
                    <p class="mt-2 text-sm text-slate-500">Consulta los préstamos de equipos y registra sus devoluciones.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-semibold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Volver al dashboard</a>
                    <a href="{{ route('asignaciones.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#4959A6]">Nueva asignación</a>
                </div>
            </header>

            @if ($errors->any())
                <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="rounded-2xl border border-indigo-100 bg-white p-4 shadow-sm sm:p-5">
                <form method="GET" action="{{ route('asignaciones.index') }}" class="grid gap-3 sm:grid-cols-[1fr_13rem_auto_auto]">
                    <label for="buscar" class="sr-only">Buscar asignaciones</label>
                    <input id="buscar" name="buscar" type="search" value="{{ request('buscar') }}" placeholder="Buscar por equipo, estudiante, matrícula o materia..." class="min-h-11 rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <label for="estado" class="sr-only">Filtrar por estado</label>
                    <select id="estado" name="estado" class="min-h-11 rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todos los estados</option>
                        <option value="activa" @selected(request('estado') === 'activa')>Activas</option>
                        <option value="finalizada" @selected(request('estado') === 'finalizada')>Finalizadas</option>
                    </select>
                    <button type="submit" class="min-h-11 rounded-xl bg-[#272F7A] px-5 text-sm font-semibold text-white transition hover:bg-[#4959A6]">Buscar</button>
                    @if (request()->filled('buscar') || request()->filled('estado'))
                        <a href="{{ route('asignaciones.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Limpiar</a>
                    @endif
                </form>
            </section>

            <section class="grid gap-5 lg:grid-cols-2">
                @forelse ($asignaciones as $asignacion)
                    <article class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-indigo-50 bg-gradient-to-r from-indigo-50 to-blue-50/60 px-5 py-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-[#4959A6]">Equipo asignado</p>
                                <h2 class="mt-1 text-xl font-bold text-slate-800">{{ $asignacion->equipo?->codigo ?? 'Equipo no disponible' }}</h2>
                                <p class="mt-1 text-sm text-slate-600">{{ $asignacion->equipo?->marca }} {{ $asignacion->equipo?->modelo }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ strtolower($asignacion->estado) === 'activa' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                {{ strtolower($asignacion->estado) === 'activa' ? 'Activa' : 'Finalizada' }}
                            </span>
                        </div>

                        <div class="space-y-5 p-5">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Estudiante</p>
                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{ $asignacion->estudiante?->nombres }}
                                        {{ $asignacion->estudiante?->apellido_paterno }}
                                        {{ $asignacion->estudiante?->apellido_materno }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">Matrícula: {{ $asignacion->estudiante?->matricula ?? 'No registrada' }}</p>
                                    <p class="text-sm text-slate-500">Semestre: {{ $asignacion->estudiante?->semestre ?? 'No registrado' }}</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Materia y horario</p>
                                    <p class="mt-1 font-semibold text-slate-800">
                                        {{ $asignacion->materia?->sigla ? $asignacion->materia->sigla.' · ' : '' }}{{ $asignacion->materia?->nombre ?? 'Materia no disponible' }}
                                    </p>
                                    @if ($asignacion->horario)
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $asignacion->horario->dia_semana }}
                                            {{ substr($asignacion->horario->hora_inicio, 0, 5) }}
                                            – {{ substr($asignacion->horario->hora_fin, 0, 5) }}
                                        </p>
                                    @else
                                        <p class="mt-1 text-sm text-slate-500">Horario no registrado</p>
                                    @endif
                                    <p class="text-sm text-slate-500">Grupo: {{ $asignacion->grupo ?: 'No registrado' }}</p>
                                </div>
                            </div>

                            <dl class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs font-semibold text-slate-500">Inicio</dt>
                                    <dd class="mt-1 text-sm font-medium text-slate-700">{{ $asignacion->fecha_inicio?->format('d/m/Y H:i') ?? 'No registrada' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold text-slate-500">Devolución</dt>
                                    <dd class="mt-1 text-sm font-medium text-slate-700">{{ $asignacion->fecha_fin?->format('d/m/Y H:i') ?? 'Pendiente' }}</dd>
                                </div>
                            </dl>

                            @if ($asignacion->observaciones)
                                <div class="rounded-xl border border-amber-100 bg-amber-50/70 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Observaciones</p>
                                    <p class="mt-1 whitespace-pre-line text-sm text-amber-900">{{ $asignacion->observaciones }}</p>
                                </div>
                            @endif

                            @if (strtolower($asignacion->estado) === 'activa')
                                <form action="{{ route('asignaciones.finalizar', $asignacion->id) }}" method="POST" onsubmit="return confirm('¿Confirmas que el equipo fue devuelto?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-800 transition hover:bg-emerald-100 sm:w-auto">Registrar devolución</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-indigo-200 bg-white px-6 py-14 text-center lg:col-span-2">
                        <p class="text-lg font-bold text-slate-700">{{ request()->filled('buscar') || request()->filled('estado') ? 'No encontramos asignaciones con esos filtros' : 'Aún no hay asignaciones registradas' }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ request()->filled('buscar') || request()->filled('estado') ? 'Prueba con otro término o limpia los filtros.' : 'Crea una asignación para registrar el préstamo de un equipo.' }}</p>
                    </div>
                @endforelse
            </section>

            <div>{{ $asignaciones->links() }}</div>
        </div>
    </main>
</x-admin-layout>

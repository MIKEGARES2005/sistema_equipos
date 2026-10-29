<x-docente-layout>
    <section class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold tracking-wider text-[#272F7A]">ITIBB · INFORMÁTICA INDUSTRIAL</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">¡Bienvenido, {{ auth()->user()->name }}!</h2>
            <p class="mt-1 text-sm text-slate-500">Este panel presenta únicamente tus materias y la actividad relacionada con ellas.</p>
        </div>
        <span class="inline-flex w-fit rounded-xl bg-indigo-100 px-4 py-2 text-sm font-bold text-[#272F7A]">Gestión vigente {{ $gestionVigenteCodigo }}</span>
    </section>

    <section aria-label="Resumen docente" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <article class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Mis materias</p>
            <p class="mt-3 text-3xl font-bold text-[#272F7A]">{{ $estadisticas['materias'] }}</p>
            <a href="{{ route('gestion-academica.index') }}" class="mt-3 inline-flex text-sm font-semibold text-[#4959A6] hover:underline">Gestión académica →</a>
        </article>
        <article class="rounded-2xl border border-blue-100 bg-gradient-to-br from-white to-blue-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Estudiantes cursando</p>
            <p class="mt-3 text-3xl font-bold text-blue-700">{{ $estadisticas['estudiantes'] }}</p>
            <a href="{{ route('panel-docente.estudiantes') }}" class="mt-3 inline-flex text-sm font-semibold text-[#4959A6] hover:underline">Abrir Panel Estudiantil →</a>
        </article>
        <article class="rounded-2xl border border-violet-100 bg-gradient-to-br from-white to-violet-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Asignaciones activas</p>
            <p class="mt-3 text-3xl font-bold text-violet-700">{{ $estadisticas['asignaciones_activas'] }}</p>
            <a href="{{ route('asignaciones.index') }}" class="mt-3 inline-flex text-sm font-semibold text-[#4959A6] hover:underline">Ver asignaciones →</a>
        </article>
        <article class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Equipos conectados</p>
            <p class="mt-3 text-3xl font-bold text-emerald-700">{{ $estadisticas['equipos_conectados'] }}</p>
            <a href="{{ route('monitoreo.index') }}" class="mt-3 inline-flex text-sm font-semibold text-[#4959A6] hover:underline">Abrir monitoreo →</a>
        </article>
        <article class="rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Incidencias pendientes</p>
            <p class="mt-3 text-3xl font-bold text-amber-700">{{ $estadisticas['incidencias_pendientes'] }}</p>
            <a href="{{ route('incidencias.index') }}" class="mt-3 inline-flex text-sm font-semibold text-[#4959A6] hover:underline">Ver incidencias →</a>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-blue-50/60 px-5 py-5 sm:px-6">
                <h3 class="text-lg font-bold text-slate-800">Mi horario</h3>
                <p class="mt-1 text-sm text-slate-500">Horarios activos asociados a tu perfil docente.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Día</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Materia</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Semestre</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Horario / aula</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($horarios as $horario)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-700">{{ \App\Models\Horario::DIAS[$horario->dia_semana] ?? $horario->dia_semana }}</td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-800">{{ $horario->materia_nombre }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $horario->semestre }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ substr($horario->hora_inicio, 0, 5) }}–{{ substr($horario->hora_fin, 0, 5) }}
                                    <span class="block text-xs text-slate-400">{{ $horario->aula ?: 'Sin aula' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">Aún no tienes horarios activos asignados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-indigo-50 bg-gradient-to-r from-indigo-50 to-blue-50/60 px-5 py-5 sm:px-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Monitoreo de mis equipos</h3>
                    <p class="mt-1 text-sm text-slate-500">Equipos vinculados a asignaciones de tus materias.</p>
                </div>
                <a href="{{ route('monitoreo.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-[#272F7A] transition hover:bg-white">Ver monitoreo</a>
            </div>
            <div class="space-y-4 p-5">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-emerald-50 p-4">
                        <p class="text-xs font-medium text-emerald-800">Conectados</p>
                        <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $estadisticas['equipos_conectados'] }}</p>
                    </div>
                    <div class="rounded-xl bg-red-50 p-4">
                        <p class="text-xs font-medium text-red-800">Desconectados</p>
                        <p class="mt-2 text-2xl font-bold text-red-700">{{ $estadisticas['equipos_desconectados'] }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-100 p-4">
                        <p class="text-xs font-medium text-slate-600">Sin datos</p>
                        <p class="mt-2 text-2xl font-bold text-slate-700">{{ $estadisticas['equipos_sin_datos'] }}</p>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <p class="text-xs font-medium text-slate-500">Último heartbeat recibido</p>
                    <p class="mt-2 text-sm font-semibold text-slate-700">{{ $ultimaActualizacionMonitoreo ? \Illuminate\Support\Carbon::parse($ultimaActualizacionMonitoreo)->format('d/m/Y H:i:s') : 'Sin datos registrados' }}</p>
                    <p class="mt-2 text-xs leading-5 text-slate-400">La conexión se calcula con la hora de recepción en el servidor; se considera reciente dentro de los últimos {{ \App\Models\Heartbeat::ONLINE_TIMEOUT_MINUTES }} minutos.</p>
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 sm:px-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Asignaciones recientes</h3>
                    <p class="mt-1 text-sm text-slate-500">Préstamos registrados en tus materias.</p>
                </div>
                <a href="{{ route('asignaciones.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-[#272F7A] transition hover:bg-indigo-50">Ver todas</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($asignacionesRecientes as $asignacion)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 sm:px-6">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $asignacion->equipo?->codigo ?? 'Equipo no disponible' }} <span class="font-normal text-slate-500">· {{ $asignacion->equipo?->marca }} {{ $asignacion->equipo?->modelo }}</span></p>
                            <p class="mt-1 text-sm text-slate-600">{{ $asignacion->estudiante?->nombres ?? 'Estudiante no disponible' }} {{ $asignacion->estudiante?->apellido_paterno }} {{ $asignacion->estudiante?->apellido_materno }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $asignacion->created_at?->format('d/m/Y H:i') ?? 'Fecha no disponible' }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ strtolower($asignacion->estado) === 'activa' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst(strtolower($asignacion->estado)) }}</span>
                    </div>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-slate-500 sm:px-6">No hay asignaciones recientes en tus materias.</p>
                @endforelse
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-red-100 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-red-50 bg-gradient-to-r from-red-50 to-orange-50/60 px-5 py-5 sm:px-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Diagnósticos críticos</h3>
                    <p class="mt-1 text-sm text-slate-500">Alertas de equipos vinculados a tus materias.</p>
                </div>
                <a href="{{ route('diagnosticos.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-[#272F7A] transition hover:bg-white">Ver diagnósticos</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($diagnosticosCriticos as $diagnostico)
                    <div class="flex items-start gap-3 px-5 py-4 sm:px-6">
                        <span class="text-lg text-red-600" aria-hidden="true">⚠</span>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">{{ $diagnostico->equipo?->codigo ?? 'Equipo no disponible' }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $diagnostico->descripcion }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $diagnostico->fecha_hora?->format('d/m/Y H:i') ?? 'Fecha no disponible' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-slate-500 sm:px-6">No hay diagnósticos críticos en los equipos vinculados a tus materias.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('panel-docente.estudiantes') }}" class="group rounded-2xl border border-indigo-100 bg-gradient-to-br from-white to-indigo-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wider text-[#4959A6]">Comunidad académica</p>
            <h3 class="mt-2 text-lg font-bold text-slate-800 group-hover:text-[#272F7A]">Panel Estudiantil</h3>
            <p class="mt-1 text-sm text-slate-500">Consulta las inscripciones cursando en tus materias de la gestión vigente.</p>
        </a>
        <a href="{{ route('panel-docente.academico') }}" class="group rounded-2xl border border-blue-100 bg-gradient-to-br from-white to-blue-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wider text-[#4959A6]">Comunidad institucional</p>
            <h3 class="mt-2 text-lg font-bold text-slate-800 group-hover:text-[#272F7A]">Panel Académico</h3>
            <p class="mt-1 text-sm text-slate-500">Consulta perfiles personales de colegas sin acceder a sus cuentas o credenciales.</p>
        </a>
    </section>

    <section class="rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-4">
        <p class="text-sm text-indigo-900">Para cambios en materias, horarios, inscripciones o en los registros personales de usuarios, solicita la actualización a administración.</p>
    </section>
</x-docente-layout>

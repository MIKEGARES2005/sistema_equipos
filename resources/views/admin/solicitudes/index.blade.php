<x-admin-layout>
    <x-admin-shell>
        <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
            <div class="space-y-6">
                @if (is_array($resultado ?? null))
                    @php
                        $resultadoColor = match ($resultado['tipo'] ?? 'info') {
                            'rechazo', 'error' => 'from-rose-700 to-rose-500',
                            'warning' => 'from-amber-700 to-amber-500',
                            default => 'from-emerald-700 to-emerald-500',
                        };
                    @endphp
                    <div x-data="{ abierto: true }" x-show="abierto" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/55 px-4 py-6 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="resultado-solicitud-titulo">
                        <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-900/10">
                            <div class="bg-gradient-to-r {{ $resultadoColor }} px-6 py-7 text-white">
                                <div class="flex items-start gap-4">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black ring-1 ring-white/30">{{ ($resultado['tipo'] ?? '') === 'rechazo' ? '×' : '✓' }}</span>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/75">Resultado de la solicitud</p>
                                        <h2 id="resultado-solicitud-titulo" class="mt-1 text-2xl font-extrabold">{{ $resultado['titulo'] ?? 'Proceso completado' }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-5 px-6 py-7">
                                <p class="text-center text-sm leading-6 text-slate-600">{{ $resultado['mensaje'] ?? '' }}</p>
                                <button type="button" @click="abierto = false" class="inline-flex w-full items-center justify-center rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#4959A6]">Cerrar</button>
                            </div>
                        </div>
                    </div>
                @endif

                <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-500">Administración / Registro</p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Solicitudes de estudiantes</h1>
                        <p class="mt-2 text-sm text-slate-500">Revisa los datos completos antes de aprobar o rechazar una solicitud.</p>
                    </div>
                    <a href="{{ route('dashboard') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-[#272F7A] shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50">Volver al dashboard</a>
                </header>

                <div class="flex flex-wrap gap-2">
                    @foreach (['pendiente' => 'Pendientes', 'aprobada' => 'Aprobadas', 'rechazada' => 'Rechazadas', 'cancelada' => 'Canceladas'] as $valor => $etiqueta)
                        <a href="{{ route('admin.solicitudes.index', ['estado' => $valor]) }}" class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $estado === $valor ? 'bg-[#272F7A] text-white shadow-sm' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">{{ $etiqueta }}</a>
                    @endforeach
                </div>

                @if ($estado === 'pendiente')
                    <form id="bulk-approve-form" method="POST" action="{{ route('admin.solicitudes.aprobar-multiples') }}">
                        @csrf
                    </form>
                @endif

                <section
                    x-data="{
                        abierta: null,
                        seleccionadas: [],
                        todas: false,
                        alternarTodas() {
                            this.seleccionadas = this.todas ? @js($solicitudes->pluck('id')->values()) : [];
                        }
                    }"
                    class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 bg-gradient-to-r from-indigo-50/70 to-white px-5 py-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-black text-slate-800">Bandeja de solicitudes</p>
                                <p class="mt-1 text-xs text-slate-500">Haz clic sobre una fila para consultar todos los datos del estudiante.</p>
                            </div>
                            @if ($estado === 'pendiente')
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="text-xs font-bold text-slate-500" x-text="seleccionadas.length + ' seleccionada(s)'">0 seleccionada(s)</span>
                                    <button
                                        type="button"
                                        @click="todas = !todas; alternarTodas()"
                                        class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-xs font-black text-indigo-700 shadow-sm transition hover:bg-indigo-50"
                                        x-text="todas ? 'Quitar selección' : 'Seleccionar todos'"
                                    >Seleccionar todos</button>
                                    <button
                                        type="submit"
                                        form="bulk-approve-form"
                                        :disabled="seleccionadas.length === 0"
                                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40"
                                    >
                                        Aprobar seleccionadas
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-wider text-slate-500">
                                <tr>
                                    @if ($estado === 'pendiente')
                                        <th class="w-12 px-5 py-4 text-center font-black">
                                            <input
                                                type="checkbox"
                                                x-model="todas"
                                                @change="alternarTodas()"
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                aria-label="Seleccionar todas las solicitudes"
                                            >
                                        </th>
                                    @endif
                                    <th class="px-5 py-4 font-black">Estudiante</th>
                                    <th class="px-5 py-4 font-black">CI</th>
                                    <th class="px-5 py-4 font-black">Matrícula</th>
                                    <th class="px-5 py-4 font-black">Contacto</th>
                                    <th class="px-5 py-4 font-black">Fecha</th>
                                    <th class="px-5 py-4 text-right font-black">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($solicitudes as $solicitud)
                                    <tr
                                        @click="abierta = abierta === {{ $solicitud->id }} ? null : {{ $solicitud->id }}"
                                        class="cursor-pointer align-top transition hover:bg-indigo-50/40"
                                        :class="abierta === {{ $solicitud->id }} ? 'bg-indigo-50/60' : ''"
                                    >
                                        @if ($estado === 'pendiente')
                                            <td class="px-5 py-4" @click.stop>
                                                <input
                                                    type="checkbox"
                                                    name="ids[]"
                                                    value="{{ $solicitud->id }}"
                                                    form="bulk-approve-form"
                                                    x-model="seleccionadas"
                                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                    aria-label="Seleccionar solicitud de {{ $solicitud->nombres }} {{ $solicitud->apellido_paterno }}"
                                                >
                                            </td>
                                        @endif
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 font-black text-indigo-700">{{ strtoupper(substr($solicitud->nombres, 0, 1).substr($solicitud->apellido_paterno, 0, 1)) }}</span>
                                                <div>
                                                    <p class="font-black text-slate-800">{{ $solicitud->nombres }} {{ $solicitud->apellido_paterno }} {{ $solicitud->apellido_materno }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">Haz clic para ver el detalle completo</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-slate-600">{{ $solicitud->ci }}{{ $solicitud->ci_extension ? ' '.$solicitud->ci_extension : '' }}</td>
                                        <td class="px-5 py-4 font-bold text-slate-700">{{ $solicitud->matricula }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ $solicitud->telefono ?: 'Sin teléfono' }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $solicitud->created_at?->format('d/m/Y H:i') }}</td>
                                        <td class="px-5 py-4">
                                            @if ($estado === 'pendiente')
                                                <span class="flex justify-end text-xs font-bold text-indigo-600">Ver detalle →</span>
                                            @else
                                                <span class="flex justify-end text-xs font-semibold capitalize text-slate-500">{{ $solicitud->estado }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr x-cloak x-show="abierta === {{ $solicitud->id }}" x-transition class="bg-indigo-50/30">
                                        <td colspan="{{ $estado === 'pendiente' ? 7 : 6 }}" class="px-5 py-5">
                                            <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                                                <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                                                    <div>
                                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-500">Ficha de la solicitud</p>
                                                        <h2 class="mt-1 text-lg font-black text-slate-800">Datos completos del estudiante</h2>
                                                    </div>
                                                    <span class="inline-flex w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-black capitalize text-amber-700">{{ $solicitud->estado }}</span>
                                                </div>
                                                <dl class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                                    @foreach ([
                                                        'Nombres' => $solicitud->nombres,
                                                        'Apellido paterno' => $solicitud->apellido_paterno,
                                                        'Apellido materno' => $solicitud->apellido_materno,
                                                        'Carnet de identidad' => trim($solicitud->ci.' '.($solicitud->ci_extension ?? '')),
                                                        'Matrícula' => $solicitud->matricula,
                                                        'Semestre' => $solicitud->semestre.'° semestre',
                                                        'Generación de ingreso' => $solicitud->generacion_ingreso,
                                                        'Teléfono' => $solicitud->telefono,
                                                        'Correo institucional' => $solicitud->correo_institucional,
                                                        'Correo personal' => $solicitud->correo_personal,
                                                        'Dirección' => $solicitud->direccion,
                                                        'Fecha de solicitud' => $solicitud->created_at?->format('d/m/Y H:i'),
                                                    ] as $etiqueta => $valor)
                                                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3">
                                                            <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $etiqueta }}</dt>
                                                            <dd class="mt-1 break-words text-sm font-semibold text-slate-700">{{ $valor ?: 'No registrado' }}</dd>
                                                        </div>
                                                    @endforeach
                                                </dl>
                                                @if ($estado === 'pendiente')
                                                    <div class="mt-5 flex flex-col justify-end gap-2 border-t border-slate-100 pt-4 sm:flex-row" @click.stop>
                                                        <form method="POST" action="{{ route('admin.solicitudes.aprobar', $solicitud->id) }}">
                                                            @csrf
                                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-black text-white transition hover:bg-emerald-700 sm:w-auto">Aprobar solicitud</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.solicitudes.rechazar', $solicitud->id) }}">
                                                            @csrf
                                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-black text-white transition hover:bg-rose-700 sm:w-auto">Rechazar solicitud</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ $estado === 'pendiente' ? 7 : 6 }}" class="px-5 py-14 text-center text-slate-500">No hay solicitudes en este estado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-4">{{ $solicitudes->links() }}</div>
                </section>
            </div>
        </main>
    </x-admin-shell>
</x-admin-layout>

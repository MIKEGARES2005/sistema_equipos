@php
    $estadoGlobal = $resumen['total'] === 0
        ? 'Sin registros'
        : ($resumen['reprobadas'] > 0 ? 'Reprobado' : ($resumen['cursando'] > 0 ? 'Cursando' : 'Aprobado'));
    $estadoClasses = [
        'Aprobado' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Reprobado' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'Cursando' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'Sin registros' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
@endphp

<x-admin-layout>
    <x-admin-shell>
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-500">Seguimiento estudiantil / Ficha</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">{{ $estudiante->nombreCompleto() }}</h1>
                    <p class="mt-2 text-sm text-slate-500">Consulta académica e historial documentado del estudiante.</p>
                </div>
                <a href="{{ route('seguimiento-estudiantil.index') }}" class="inline-flex w-fit items-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-[#272F7A] ring-1 ring-indigo-200 transition hover:bg-indigo-50">← Volver al seguimiento</a>
            </header>

            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="rounded-3xl bg-[#272F7A] p-5 text-white shadow-lg sm:p-7">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-indigo-200">Perfil en seguimiento</p>
                        <h2 class="mt-2 text-2xl font-black">{{ $estudiante->nombreCompleto() }}</h2>
                        <p class="mt-1 text-sm text-indigo-100">CI {{ $estudiante->ci }} · {{ $estudiante->matricula ?: 'Sin matrícula' }}</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-white/15 px-4 py-2 text-sm font-black ring-1 ring-white/20">Estado: {{ ucfirst($estudiante->estado) }}</span>
                </div>
                <div class="mt-6 grid gap-3 sm:grid-cols-4">
                    @foreach ([['label' => 'Semestre', 'value' => $estudiante->semestre], ['label' => 'Gestión consultada', 'value' => $gestionVigente], ['label' => 'Correo institucional', 'value' => $estudiante->correo_institucional ?: 'No registrado'], ['label' => 'Cuenta', 'value' => $estudiante->user?->activo ? 'Activa' : 'Inactiva']] as $dato)
                        <div class="rounded-2xl bg-white/10 p-3 ring-1 ring-white/10">
                            <p class="text-[10px] font-black uppercase tracking-wider text-indigo-200">{{ $dato['label'] }}</p>
                            <p class="mt-1 truncate text-sm font-bold text-white">{{ $dato['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="grid gap-4 sm:grid-cols-4">
                @foreach ([['label' => 'Estado global', 'value' => $estadoGlobal, 'class' => $estadoClasses[$estadoGlobal]], ['label' => 'Materias', 'value' => $resumen['total'], 'class' => 'bg-indigo-50 text-indigo-700 ring-indigo-200'], ['label' => 'Aprobadas', 'value' => $resumen['aprobadas'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'], ['label' => 'Reprobadas / cursando', 'value' => $resumen['reprobadas'].' / '.$resumen['cursando'], 'class' => 'bg-amber-50 text-amber-700 ring-amber-200']] as $dato)
                    <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $dato['label'] }}</p>
                        <p class="mt-3 inline-flex rounded-xl px-3 py-2 text-lg font-black ring-1 {{ $dato['class'] }}">{{ $dato['value'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
                <article class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-gradient-to-r from-indigo-50/70 to-white px-5 py-4">
                        <h2 class="text-sm font-black text-slate-800">Materias, docentes y notas</h2>
                        <p class="mt-1 text-xs text-slate-500">La actualización de notas, estados y cambio de semestre se realiza en Gestión académica.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500">
                                <tr><th class="px-5 py-3">Materia</th><th class="px-5 py-3">Docente / horario</th><th class="px-5 py-3">Notas</th><th class="px-5 py-3">Estado</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($inscripciones as $inscripcion)
                                    @php
                                        $horario = $inscripcion->materia?->horarios->first(fn ($item) => $item->gestion_id === $inscripcion->gestion_id || $item->gestion === $inscripcion->gestion);
                                        $estado = $inscripcion->estado ?: 'Cursando';
                                        $estadoClass = $estado === 'Aprobado' ? 'bg-emerald-50 text-emerald-700' : ($estado === 'Reprobado' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700');
                                    @endphp
                                    <tr class="align-top">
                                        <td class="px-5 py-4"><p class="font-black text-slate-800">{{ $inscripcion->materia?->nombre ?: 'Materia sin nombre' }}</p><p class="mt-1 text-xs text-slate-400">{{ $inscripcion->materia?->sigla ?: 'Sin sigla' }} · {{ $inscripcion->gestion ?: $inscripcion->gestionAcademica?->codigo }}</p></td>
                                        <td class="px-5 py-4 text-xs text-slate-600"><p class="font-bold">{{ $horario?->docente?->nombreCompleto() ?: 'Sin docente asignado' }}</p><p class="mt-1">{{ $horario ? $horario->dia_semana.' · '.$horario->hora_inicio.' - '.$horario->hora_fin.' · Aula '.$horario->aula : 'Sin horario asignado' }}</p></td>
                                        <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-600"><p>P1: <strong>{{ $inscripcion->parcial_1 ?? '—' }}</strong></p><p class="mt-1">P2: <strong>{{ $inscripcion->parcial_2 ?? '—' }}</strong></p><p class="mt-1">Final: <strong>{{ $inscripcion->nota_final ?? '—' }}</strong></p></td>
                                        <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-[11px] font-black {{ $estadoClass }}">{{ $estado }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-slate-500">Todavía no hay inscripciones ni notas registradas para este estudiante.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article x-data="{ tipoEvento: 'observacion', confirmarAbandono: false }" class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                    <div>
                        <h2 class="text-sm font-black text-slate-800">Registrar evento</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">Documenta situaciones administrativas o de acompañamiento. No modifica notas ni semestre.</p>
                    </div>
                    <form x-ref="formulario" method="POST" action="{{ route('seguimiento-estudiantil.events.store', $estudiante) }}" class="mt-5 space-y-4" @submit.prevent="tipoEvento === 'abandono' ? confirmarAbandono = true : $refs.formulario.submit()">
                        @csrf
                        <div>
                            <label for="tipo_evento" class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500">Evento</label>
                            <select id="tipo_evento" name="tipo_evento" x-model="tipoEvento" required class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="observacion">Observación</option>
                                <option value="congelamiento">Congelamiento</option>
                                <option value="reincorporacion">Reincorporación</option>
                                <option value="retiro">Retiro</option>
                                <option value="abandono">Abandono</option>
                            </select>
                        </div>
                        <div>
                            <label for="fecha_evento" class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500">Fecha</label>
                            <input id="fecha_evento" name="fecha_evento" type="date" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="rounded-xl bg-indigo-50 px-3 py-2.5 text-xs font-semibold leading-5 text-indigo-700">
                            Este evento se vinculará automáticamente a la gestión vigente: <strong>{{ $gestionVigente }}</strong>.
                        </div>
                        <div>
                            <label for="descripcion" class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500">Motivo / descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="4" maxlength="1000" required placeholder="Explica el motivo o la observación" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#272F7A] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#4959A6]">Guardar evento</button>

                        <div x-cloak x-show="confirmarAbandono" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true">
                            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                                <h3 class="text-lg font-black text-slate-900">Confirmar abandono</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Esta acción documentará el abandono y eliminará lógicamente el perfil y su usuario. El historial quedará conservado.</p>
                                <div class="mt-5 flex justify-end gap-3">
                                    <button type="button" @click="confirmarAbandono = false" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-700">Cancelar</button>
                                    <button type="button" @click="$refs.formulario.submit()" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white">Confirmar abandono</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </article>
            </section>

            <section class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-sm font-black text-slate-800">Historial de eventos</h2>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @forelse ($estudiante->historialSeguimiento as $evento)
                        <article class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-indigo-700">{{ str_replace('_', ' ', $evento->tipo_evento) }}</span><time class="text-xs font-semibold text-slate-400">{{ $evento->fecha_evento?->format('d/m/Y') }}</time></div>
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $evento->descripcion ?: 'Sin descripción adicional.' }}</p>
                            <p class="mt-2 text-xs font-semibold text-slate-400">Registrado por {{ $evento->registradoPor?->username ?: 'sistema' }} · {{ $evento->gestion?->codigo ?: 'Sin gestión' }}</p>
                        </article>
                    @empty
                        <p class="rounded-xl border border-dashed border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">Este estudiante todavía no tiene eventos registrados.</p>
                    @endforelse
                </div>
            </section>
        </main>
    </x-admin-shell>
</x-admin-layout>

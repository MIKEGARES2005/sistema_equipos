<x-admin-layout>
    <x-admin-shell>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70">
        <main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <header class="space-y-4">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-800">{{ $semestre }} semestre</h1>
                        <p class="mt-2 text-sm text-slate-500">Materias, horarios y estado académico de las inscripciones.</p>
                    </div>
                    <div class="flex flex-wrap items-end gap-3">
                        <form method="GET" action="{{ route('gestion-academica.semestre', $semestre) }}" class="flex items-end gap-2">
                            <div>
                                <label for="gestion" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Consultar historial</label>
                                <select id="gestion" name="gestion" onchange="this.form.submit()" class="rounded-xl border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($gestiones as $opcionGestion)
                                        <option value="{{ $opcionGestion }}" @selected($gestion === $opcionGestion)>{{ $opcionGestion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                        <a href="{{ route('gestion-academica.index') }}" class="inline-flex items-center rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-[#272F7A] shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50">
                            Volver al listado
                        </a>
                        @if (auth()->user()->isAdministrador())
                            <a href="{{ route('materias.create', ['semestre' => $semestre, 'gestion' => $gestion]) }}"
                               class="rounded-xl bg-[#272F7A] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#4959A6]">Nueva materia</a>
                        @endif
                    </div>
                </div>
                @if (auth()->user()->isAdministrador())
                    <form method="POST" action="{{ route('gestion-academica.gestion-vigente') }}" class="flex flex-col gap-4 rounded-2xl border border-indigo-200 bg-gradient-to-r from-indigo-50 to-blue-50 p-4 shadow-sm sm:flex-row sm:items-end sm:justify-between">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="semestre" value="{{ $semestre }}">
                        <div>
                            <label for="gestion_vigente" class="block text-sm font-bold text-[#272F7A]">Gestión vigente para todos los semestres</label>
                            <p class="mt-1 max-w-2xl text-xs leading-5 text-slate-600">Al actualizarla, será la gestión predeterminada de Primero, Segundo y Tercero. Las inscripciones anteriores conservarán su gestión registrada.</p>
                            <input id="gestion_vigente" name="gestion_vigente" type="text" list="gestiones-vigentes" value="{{ old('gestion_vigente', $gestionVigente) }}" pattern="[0-9]{4}-(I|II)" placeholder="Ej. 2027-I" required class="mt-3 w-full rounded-xl border-indigo-200 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-44">
                            <datalist id="gestiones-vigentes">
                                @foreach ($gestionesVigentes as $opcionGestion)
                                    <option value="{{ $opcionGestion }}">
                                @endforeach
                            </datalist>
                        </div>
                        <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-[#272F7A] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#4959A6]">
                            Actualizar para todos
                        </button>
                    </form>
                @endif
            </header>

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @forelse ($materias as $materia)
                <section class="overflow-hidden rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50/80 via-white to-blue-50/80 shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-indigo-300/40 bg-gradient-to-r from-[#272F7A] to-[#4959A6] px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white" aria-hidden="true">▤</div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-lg font-bold text-white">{{ $materia->nombre }}</h2>
                                    @if ($materia->sigla)
                                        <span class="rounded-lg bg-white/15 px-2.5 py-1 text-xs font-extrabold uppercase tracking-wide text-white ring-1 ring-white/20">{{ $materia->sigla }}</span>
                                    @else
                                        <span class="rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">Sin sigla</span>
                                    @endif
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ in_array(strtolower($materia->estado), ['activo', 'activa'], true) ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ in_array(strtolower($materia->estado), ['activo', 'activa'], true) ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-indigo-100">{{ $materia->semestre }} semestre · {{ $gestion }}</p>
                            </div>
                        </div>
                        @if (auth()->user()->isAdministrador())
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('materias.edit', ['materia' => $materia, 'gestion' => $gestion]) }}"
                                   class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-bold text-[#272F7A] hover:bg-indigo-100">Editar materia</a>
                                <form method="POST" action="{{ route('materias.status', $materia) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado" value="{{ in_array(strtolower($materia->estado), ['activo', 'activa'], true) ? 'inactivo' : 'activo' }}">
                                    <input type="hidden" name="gestion" value="{{ $gestion }}">
                                    <button type="submit" class="rounded-lg px-3 py-2 text-xs font-bold {{ in_array(strtolower($materia->estado), ['activo', 'activa'], true) ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                        {{ in_array(strtolower($materia->estado), ['activo', 'activa'], true) ? 'Inhabilitar' : 'Habilitar' }}
                                    </button>
                                </form>
                                <a href="{{ route('horarios.create', ['semestre' => $semestre, 'materia_id' => $materia->id, 'gestion' => $gestion]) }}"
                                   class="rounded-lg bg-[#272F7A] px-3 py-2 text-xs font-bold text-white hover:bg-[#4959A6]">Asignar horario</a>
                                <a href="{{ route('inscripciones.create', ['semestre' => $semestre, 'materia_id' => $materia->id, 'gestion' => $gestion]) }}"
                                   class="rounded-lg bg-indigo-100 px-3 py-2 text-xs font-bold text-[#272F7A] hover:bg-indigo-200">Inscribir estudiante</a>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-6 p-6 xl:grid-cols-[0.9fr_1.4fr]">
                        <div>
                            <div class="mb-3 flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800">Docentes y horarios</h3>
                                    <p class="mt-1 text-xs text-slate-500">Programación de esta materia.</p>
                                </div>
                                @if (auth()->user()->isAdministrador())
                                    <a href="{{ route('horarios.create', ['semestre' => $semestre, 'materia_id' => $materia->id, 'gestion' => $gestion]) }}" class="text-xs font-semibold text-[#4959A6] hover:underline">Añadir</a>
                                @endif
                            </div>
                            <div class="space-y-3">
                                @forelse ($materia->horarios as $horario)
                                    <div class="rounded-xl border border-blue-200 bg-blue-50/80 p-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-800">
                                                    {{ trim(($horario->docente?->nombres ?? '').' '.($horario->docente?->apellido_paterno ?? '').' '.($horario->docente?->apellido_materno ?? '')) ?: 'Docente no disponible' }}
                                                </p>
                                                <p class="mt-1 text-sm text-slate-600">
                                                    {{ $horario->dia_semana }} · {{ substr($horario->hora_inicio, 0, 5) }}–{{ substr($horario->hora_fin, 0, 5) }}
                                                    @if ($horario->aula) · {{ $horario->aula }} @endif
                                                </p>
                                            </div>
                                            <span class="w-fit shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ in_array(strtolower($horario->estado), ['activo', 'activa'], true) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                                {{ in_array(strtolower($horario->estado), ['activo', 'activa'], true) ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </div>
                                        @if (auth()->user()->isAdministrador())
                                            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-blue-200 pt-3">
                                                <a href="{{ route('horarios.edit', ['horario' => $horario, 'gestion' => $gestion]) }}" class="inline-flex min-h-9 items-center rounded-lg border border-indigo-200 bg-white px-3 py-2 text-xs font-semibold text-[#272F7A] transition hover:bg-indigo-50">Editar horario</a>
                                                <form method="POST" action="{{ route('horarios.status', $horario) }}" class="inline-flex">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="estado" value="{{ in_array(strtolower($horario->estado), ['activo', 'activa'], true) ? 'inactivo' : 'activo' }}">
                                                    <input type="hidden" name="gestion" value="{{ $gestion }}">
                                                    <button type="submit" class="inline-flex min-h-9 items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100">{{ in_array(strtolower($horario->estado), ['activo', 'activa'], true) ? 'Inhabilitar' : 'Habilitar' }}</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="rounded-xl border border-dashed border-slate-300 px-4 py-5 text-sm text-slate-500">Aún no hay horarios registrados.</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <div class="mb-3">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800">Estudiantes inscritos</h3>
                                    <p class="mt-1 text-xs text-slate-500">Inscripciones registradas durante {{ $gestion }}.</p>
                                </div>
                            </div>
                            <div class="overflow-x-auto rounded-xl border border-indigo-200">
                                <table class="w-full min-w-[980px] text-left">
                                    <thead class="bg-indigo-50">
                                        <tr>
                                            <th class="px-4 py-3 align-middle text-[11px] font-bold uppercase tracking-wider text-slate-500">Estudiante</th>
                                            <th class="px-4 py-3 align-middle text-[11px] font-bold uppercase tracking-wider text-slate-500">Tipo</th>
                                            <th class="px-4 py-3 align-middle text-[11px] font-bold uppercase tracking-wider text-slate-500">P1</th>
                                            <th class="px-4 py-3 align-middle text-[11px] font-bold uppercase tracking-wider text-slate-500">P2</th>
                                            <th class="px-4 py-3 align-middle text-[11px] font-bold uppercase tracking-wider text-slate-500">Nota final</th>
                                            <th class="px-4 py-3 align-middle text-[11px] font-bold uppercase tracking-wider text-slate-500">Estado final</th>
                                            <th class="px-4 py-3 align-middle text-[11px] font-bold uppercase tracking-wider text-slate-500">Reporte docente</th>
                                            <th class="px-4 py-3 text-right align-middle text-[11px] font-bold uppercase tracking-wider text-slate-500">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($materia->inscripciones as $inscripcion)
                                            @php
                                                $esDocenteAsignado = auth()->user()->isDocente() && ! auth()->user()->isAdministrador();
                                                $puedeCalificar = $esDocenteAsignado && $inscripcion->estado === 'Cursando';
                                                $notaMinima = (float) config('academica.nota_minima_aprobacion', 56);
                                            @endphp
                                            <tr class="bg-white/70 transition hover:bg-indigo-50/50">
                                                <td class="px-4 py-3 align-middle">
                                                    <p class="text-sm font-semibold text-slate-800">{{ trim(($inscripcion->estudiante?->nombres ?? '').' '.($inscripcion->estudiante?->apellido_paterno ?? '').' '.($inscripcion->estudiante?->apellido_materno ?? '')) }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $inscripcion->estudiante?->matricula ?? 'Sin matrícula' }}</p>
                                                </td>
                                                <td class="whitespace-nowrap px-4 py-3 align-middle text-sm text-slate-600">{{ $inscripcion->tipo }}</td>
                                                <td class="px-4 py-3 align-middle">
                                                    @if ($puedeCalificar)
                                                        <form method="POST" action="{{ route('inscripciones.register-partial', $inscripcion) }}" class="flex items-center gap-1.5">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="parcial" value="1">
                                                            <input type="number" name="nota" min="0" max="100" step="0.01" value="{{ $inscripcion->parcial_1 !== null ? number_format((float) $inscripcion->parcial_1, 2, '.', '') : '' }}" aria-label="Primer parcial" required class="w-20 rounded-lg border-slate-300 py-1 text-xs">
                                                            <button type="submit" class="rounded-lg bg-[#272F7A] px-2 py-1 text-[11px] font-semibold text-white hover:bg-[#4959A6]">Guardar</button>
                                                        </form>
                                                    @else
                                                        <span class="text-sm font-semibold text-slate-700">{{ $inscripcion->parcial_1 !== null ? number_format((float) $inscripcion->parcial_1, 2) : '—' }}</span>
                                                    @endif
                                                    @if ($inscripcion->primerParcialPor)
                                                        <p class="mt-1 text-[11px] text-slate-400">{{ $inscripcion->primerParcialPor->name }}</p>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 align-middle">
                                                    @if ($puedeCalificar && $inscripcion->parcial_1 !== null)
                                                        <form method="POST" action="{{ route('inscripciones.register-partial', $inscripcion) }}" class="flex items-center gap-1.5">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="parcial" value="2">
                                                            <input type="number" name="nota" min="0" max="100" step="0.01" value="{{ $inscripcion->parcial_2 !== null ? number_format((float) $inscripcion->parcial_2, 2, '.', '') : '' }}" aria-label="Segundo parcial" required class="w-20 rounded-lg border-slate-300 py-1 text-xs">
                                                            <button type="submit" class="rounded-lg bg-[#272F7A] px-2 py-1 text-[11px] font-semibold text-white hover:bg-[#4959A6]">Guardar</button>
                                                        </form>
                                                    @elseif ($puedeCalificar)
                                                        <span class="text-[11px] text-slate-400">Registra P1 primero</span>
                                                    @else
                                                        <span class="text-sm font-semibold text-slate-700">{{ $inscripcion->parcial_2 !== null ? number_format((float) $inscripcion->parcial_2, 2) : '—' }}</span>
                                                    @endif
                                                    @if ($inscripcion->segundoParcialPor)
                                                        <p class="mt-1 text-[11px] text-slate-400">{{ $inscripcion->segundoParcialPor->name }}</p>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 align-middle">
                                                    @if ($inscripcion->nota_final !== null)
                                                        <span class="text-sm font-bold {{ (float) $inscripcion->nota_final >= $notaMinima ? 'text-emerald-700' : 'text-red-700' }}">{{ number_format((float) $inscripcion->nota_final, 2) }}</span>
                                                        <p class="mt-1 text-[11px] text-slate-400">Mínima {{ number_format($notaMinima, 0) }}</p>
                                                    @else
                                                        <span class="text-xs text-slate-400">Pendiente</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 align-middle">
                                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold
                                                        {{ match ($inscripcion->estado) {
                                                            'Aprobado' => 'bg-emerald-50 text-emerald-700',
                                                            'Reprobado' => 'bg-red-50 text-red-700',
                                                            'Inactivo' => 'bg-slate-100 text-slate-600',
                                                            default => 'bg-blue-50 text-blue-700',
                                                        } }}">
                                                        {{ $inscripcion->estado }}
                                                    </span>
                                                    @if ($inscripcion->revisadoPor)
                                                        <p class="mt-1 text-[11px] text-slate-400">Revisado por {{ $inscripcion->revisadoPor->name }}</p>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 align-middle">
                                                    @if ($inscripcion->resultado_reportado)
                                                        <p class="text-sm font-semibold text-slate-700">{{ $inscripcion->resultado_reportado }}</p>
                                                        <p class="mt-1 text-[11px] text-slate-400">
                                                            {{ $inscripcion->reportadoPor?->name ?? 'Docente' }}
                                                            @if ($inscripcion->fecha_reporte) · {{ $inscripcion->fecha_reporte->format('d/m/Y') }} @endif
                                                        </p>
                                                    @else
                                                        <span class="text-xs text-slate-400">Pendiente</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 align-middle">
                                                    @if (auth()->user()->isAdministrador())
                                                        <div class="flex flex-col items-end gap-2">
                                                            @if ($inscripcion->resultado_reportado && $inscripcion->estado === 'Cursando')
                                                                <form method="POST" action="{{ route('inscripciones.status', $inscripcion) }}" class="flex items-center gap-2">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <select name="estado" aria-label="Definir decisión académica" required class="rounded-lg border-slate-300 py-1 text-xs">
                                                                        <option value="Aprobado" @selected($inscripcion->resultado_reportado === 'Aprobado')>Aprobado</option>
                                                                        <option value="Reprobado" @selected($inscripcion->resultado_reportado === 'Reprobado')>Reprobado</option>
                                                                    </select>
                                                                    <button type="submit" class="inline-flex min-h-8 items-center rounded-lg bg-[#272F7A] px-2.5 text-xs font-semibold text-white transition hover:bg-[#4959A6]">Definir</button>
                                                                </form>
                                                            @endif
                                                            <a href="{{ route('inscripciones.edit', ['inscripcion' => $inscripcion, 'gestion' => $gestion]) }}" class="inline-flex min-h-9 items-center rounded-lg border border-indigo-200 bg-white px-3 text-xs font-semibold text-[#272F7A] transition hover:bg-indigo-50">Editar</a>
                                                        </div>
                                                    @elseif ($puedeCalificar)
                                                        @if ($inscripcion->resultado_reportado)
                                                            <span class="block text-right text-xs font-semibold text-amber-600">En revisión</span>
                                                        @elseif ($inscripcion->tieneAmbosParciales())
                                                            <form method="POST" action="{{ route('inscripciones.report-result', $inscripcion) }}" class="flex min-w-[180px] items-center justify-end gap-2">
                                                                @csrf
                                                                @method('PATCH')
                                                                <select name="resultado_reportado" aria-label="Reportar resultado académico" required class="rounded-lg border-slate-300 py-1 text-xs">
                                                                    <option value="">Reportar...</option>
                                                                    <option value="Aprobado" @selected($inscripcion->resultadoSegunNotaFinal() === 'Aprobado')>Aprobado</option>
                                                                    <option value="Reprobado" @selected($inscripcion->resultadoSegunNotaFinal() === 'Reprobado')>Reprobado</option>
                                                                </select>
                                                                <button type="submit" class="inline-flex min-h-8 items-center rounded-lg bg-[#272F7A] px-2.5 text-xs font-semibold text-white transition hover:bg-[#4959A6]">Enviar</button>
                                                            </form>
                                                        @else
                                                            <span class="block text-right text-xs text-slate-400">Registra P1 y P2</span>
                                                        @endif
                                                    @else
                                                        <span class="block text-right text-xs text-slate-400">Solo lectura</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">No hay estudiantes inscritos en esta materia durante {{ $gestion }}.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                    <h2 class="text-lg font-bold text-slate-700">Todavía no hay materias asignadas</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        @if (auth()->user()->isDocente())
                            No hay materias de este semestre vinculadas a tus horarios.
                        @else
                            Registra una materia para comenzar a organizar el semestre.
                        @endif
                    </p>
                    @if (auth()->user()->isAdministrador())
                        <a href="{{ route('materias.create', ['semestre' => $semestre, 'gestion' => $gestion]) }}" class="mt-5 inline-flex rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white hover:bg-[#4959A6]">Crear materia</a>
                    @endif
                </div>
            @endforelse
        </main>
    </div>
    </x-admin-shell>
</x-admin-layout>

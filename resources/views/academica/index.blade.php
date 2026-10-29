<x-admin-layout>
    <x-admin-shell>
    <div class="min-h-screen bg-slate-50">
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold text-[#4959A6]">Administración / Académica</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-800">Gestión académica</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">
                        Selecciona un semestre para consultar sus materias, docentes, horarios e inscripciones del periodo vigente.
                    </p>
                    <a href="{{ route('dashboard') }}"
                       class="mt-4 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-[#272F7A]">
                        <span aria-hidden="true"></span>
                        Volver al dashboard
                    </a>
                </div>
                <div class="inline-flex items-center gap-3 self-start rounded-2xl border border-indigo-100 bg-white px-4 py-3 shadow-sm sm:self-auto">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-[#272F7A]" aria-hidden="true">◷</span>
                    <span>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Gestión vigente</span>
                        <span class="mt-0.5 block text-lg font-bold text-slate-800">{{ $gestion }}</span>
                    </span>
                </div>
            </header>

            <div class="mb-5 flex flex-col gap-1">
                <h2 class="text-xl font-bold text-slate-800">Semestres del laboratorio</h2>
                <p class="text-sm text-slate-500">Elige una tarjeta para abrir su información académica.</p>
            </div>
            <div class="mb-6 flex gap-3 rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-4 text-sm text-indigo-900">
                <span class="mt-0.5 text-lg" aria-hidden="true">ⓘ</span>
                <p>
                Los resultados reportados por el docente requieren revisión administrativa. El avance o repetición de semestre no se calcula ni se aplica automáticamente.
                </p>
            </div>

            <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3" aria-label="Seleccionar semestre">
                @foreach ($semestres as $indice => $resumen)
                    <a href="{{ route('gestion-academica.semestre', ['semestre' => $resumen['nombre'], 'gestion' => $gestion]) }}"
                       class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200">
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#272F7A] via-indigo-500 to-cyan-400"></div>
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-xl font-extrabold text-[#272F7A] ring-1 ring-indigo-100">
                                0{{ $indice + 1 }}
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                {{ $gestion }}
                            </span>
                        </div>
                        <h3 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-800 group-hover:text-[#272F7A]">{{ $resumen['nombre'] }} semestre</h3>
                        <p class="mt-2 min-h-10 text-sm leading-5 text-slate-500">
                            @if (auth()->user()->isDocente())
                                Consulta tus materias, horarios y estudiantes asignados.
                            @else
                                Administra las materias, horarios e inscripciones de este semestre.
                            @endif
                        </p>
                        <div class="mt-6 grid grid-cols-3 gap-2">
                            <div class="rounded-xl bg-slate-50 px-3 py-3 transition group-hover:bg-indigo-50/70">
                                <p class="text-2xl font-extrabold text-slate-800">{{ $resumen['materias'] }}</p>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Materias</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-3 transition group-hover:bg-indigo-50/70">
                                <p class="text-2xl font-extrabold text-slate-800">{{ $resumen['horarios'] }}</p>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Horarios</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-3 transition group-hover:bg-indigo-50/70">
                                <p class="text-2xl font-extrabold text-slate-800">{{ $resumen['inscripciones'] }}</p>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Inscritos</p>
                            </div>
                        </div>
                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                            <span class="text-sm font-bold text-[#4959A6] group-hover:text-[#272F7A]">Abrir semestre</span>
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-lg font-semibold text-[#272F7A] transition group-hover:translate-x-1 group-hover:bg-[#272F7A] group-hover:text-white" aria-hidden="true">→</span>
                        </div>
                    </a>
                @endforeach
            </section>
        </main>
    </div>
    </x-admin-shell>
</x-admin-layout>

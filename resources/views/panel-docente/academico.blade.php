<x-docente-layout>
    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-[#4959A6]">Panel docente / Comunidad institucional</p>
            <h2 class="mt-1 text-3xl font-bold text-slate-800">Panel Académico</h2>
            <p class="mt-2 text-sm text-slate-500">Consulta los perfiles personales de tus colegas docentes registrados.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-semibold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Volver al inicio</a>
    </section>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
        Esta sección muestra datos del perfil personal docente. No incluye usuarios, credenciales, contraseñas ni información de acceso al sistema.
    </div>

    <section class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-6">
        <form method="GET" action="{{ route('panel-docente.academico') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="flex-1">
                <label for="buscar" class="sr-only">Buscar colegas</label>
                <input id="buscar" name="buscar" type="search" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Buscar por nombre, CI, teléfono, cargo o nivel académico" class="min-h-11 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <button type="submit" class="min-h-11 rounded-xl bg-[#272F7A] px-5 text-sm font-semibold text-white transition hover:bg-[#4959A6]">Buscar</button>
            @if (($filtros['buscar'] ?? '') !== '')
                <a href="{{ route('panel-docente.academico') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Limpiar</a>
            @endif
        </form>
    </section>

    <section aria-label="Perfiles personales docentes" class="grid gap-5 lg:grid-cols-2">
        @forelse ($docentes as $docente)
            <article class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
                <div class="flex items-center gap-4 border-b border-indigo-50 bg-gradient-to-r from-indigo-50 to-blue-50/60 p-5">
                    @if ($docente->foto_personal)
                        <img src="{{ asset('storage/'.$docente->foto_personal) }}" alt="Fotografía de {{ $docente->nombres }}" class="h-16 w-16 rounded-2xl border border-white object-cover shadow-sm">
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#272F7A] text-xl font-bold text-white" aria-hidden="true">{{ mb_substr($docente->nombres, 0, 1).mb_substr($docente->apellido_paterno, 0, 1) }}</div>
                    @endif
                    <div class="min-w-0">
                        <h3 class="text-lg font-bold text-slate-800">{{ $docente->nombres }} {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}</h3>
                        <p class="mt-1 text-sm text-[#4959A6]">{{ $docente->cargo }}</p>
                    </div>
                    <span class="ml-auto shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ strtolower($docente->estado) === 'activo' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($docente->estado) }}</span>
                </div>
                <dl class="grid gap-x-5 gap-y-4 p-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Documento de identidad</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $docente->ci ?: 'No registrado' }}{{ $docente->ci_extension ? ' '.$docente->ci_extension : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Teléfono</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $docente->telefono ?: 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nivel académico</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $docente->nivel_academico ?: 'No registrado' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Fecha de ingreso</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-700">{{ $docente->fecha_ingreso ? \Illuminate\Support\Carbon::parse($docente->fecha_ingreso)->format('d/m/Y') : 'No registrada' }}</dd>
                    </div>
                </dl>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-indigo-200 bg-white px-6 py-14 text-center lg:col-span-2">
                <p class="text-lg font-bold text-slate-700">{{ ($filtros['buscar'] ?? '') !== '' ? 'No encontramos colegas con esa búsqueda' : 'Aún no hay colegas docentes registrados' }}</p>
                <p class="mt-2 text-sm text-slate-500">Los perfiles personales de docentes registrados aparecerán en esta lista.</p>
            </div>
        @endforelse
    </section>

    <div>{{ $docentes->links() }}</div>
</x-docente-layout>

<x-admin-layout>
    <x-admin-shell>
        @php
            $pestanas = [
                'administrador' => 'Administradores',
                'docente' => 'Docentes',
                'estudiante' => 'Estudiantes',
            ];
            $iconosCategoria = [
                'administrador' => 'shield',
                'docente' => 'academic-cap',
                'estudiante' => 'users',
            ];
            $descripcionesCategoria = [
                'administrador' => 'Control total del sistema',
                'docente' => 'Gestión académica',
                'estudiante' => 'Portal estudiantil',
            ];
            $etiquetaCategoria = $pestanas[$categoria] ?? 'Usuarios';
        @endphp

        <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
            {{-- ENCABEZADO --}}
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-500">
                        Administración / Personas y usuarios
                    </p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                        Personas y usuarios
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Administra las cuentas de administradores, docentes y estudiantes.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('usuarios.create') }}"
                       class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#1E2A5A] to-[#272F7A] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:from-[#272F7A] hover:to-[#4959A6]">
                        <x-icon name="plus" class="h-4 w-4" />
                        Crear perfil y cuenta
                    </a>
                    <a href="{{ route('usuarios.deleted') }}"
                       class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#1E2A5A] to-[#272F7A] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:from-[#272F7A] hover:to-[#4959A6]">
                        Perfiles eliminados
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- CATEGORÍAS --}}
            <nav class="grid gap-2 sm:grid-cols-3" aria-label="Categorías de usuarios">
                @foreach ($pestanas as $valor => $etiqueta)
                    @php
                        $activa = $categoria === $valor;
                    @endphp

                    <a href="{{ route('usuarios.index', ['categoria' => $valor]) }}"
                       @if ($activa) aria-current="page" @endif
                       class="group flex min-w-0 items-center justify-between gap-3 rounded-2xl border px-4 py-3 transition
                       {{ $activa
                            ? 'border-[#1E2A5A] bg-gradient-to-br from-[#1E2A5A] to-[#272F7A] text-white shadow-lg shadow-indigo-950/10'
                            : 'border-slate-200 bg-white text-slate-700 shadow-sm hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md' }}">
                        <span class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                                {{ $activa
                                    ? 'bg-white/10 text-cyan-200 ring-1 ring-white/15'
                                    : 'bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100' }}">
                                <x-icon :name="$iconosCategoria[$valor]" class="h-4 w-4" />
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-black">{{ $etiqueta }}</span>
                                <span class="mt-0.5 block truncate text-[11px] {{ $activa ? 'text-indigo-100/75' : 'text-slate-400' }}">
                                    {{ $descripcionesCategoria[$valor] }}
                                </span>
                            </span>
                        </span>

                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-black
                            {{ $activa
                                ? 'bg-white/15 text-white ring-1 ring-white/15'
                                : 'bg-slate-50 text-slate-500 ring-1 ring-slate-200' }}">
                            {{ $conteos[$valor] ?? 0 }}
                        </span>
                    </a>
                @endforeach
            </nav>

            {{-- FILTROS --}}
            <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
                <form action="{{ route('usuarios.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <input type="hidden" name="categoria" value="{{ $categoria }}">

                    @if ($categoria === 'estudiante')
                        <div class="sm:w-44">
                            <label for="semestre" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                Semestre
                            </label>
                            <select id="semestre"
                                    name="semestre"
                                    class="w-full rounded-xl border-slate-300 py-2.5 pl-3 pr-8 text-sm font-semibold text-slate-700 focus:border-[#4959A6] focus:ring-[#4959A6]">
                                <option value="todos" @selected($semestre === null)>Todos los semestres</option>
                                @foreach ($semestresPermitidos as $s)
                                    <option value="{{ $s }}" @selected($semestre === $s)>
                                        {{ $s }}° semestre
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <label for="buscar" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            Buscar en {{ strtolower($etiquetaCategoria) }}
                        </label>
                        <div class="relative">
                            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input id="buscar"
                                   type="text"
                                   name="buscar"
                                   value="{{ $buscar }}"
                                   placeholder="{{ $categoria === 'estudiante' ? 'Nombre, usuario o matrícula...' : 'Nombre o usuario...' }}"
                                   class="w-full rounded-xl border-slate-300 py-2.5 pl-10 pr-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-[#272F7A] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#4959A6] sm:flex-none">
                            <x-icon name="filter" class="h-4 w-4" />
                            Filtrar
                        </button>

                        @if ($buscar || ($categoria === 'estudiante' && $semestre !== null))
                            <a href="{{ route('usuarios.index', ['categoria' => $categoria]) }}"
                               class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>
            </section>

            {{-- TABLA --}}
            <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                    <div class="min-w-0">
                        <h2 class="truncate text-sm font-black text-slate-900">
                            @if ($categoria === 'estudiante')
                                Estudiantes de {{ $semestre }}° semestre
                            @else
                                {{ $etiquetaCategoria }}
                            @endif
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500">
                            @if ($categoria === 'estudiante')
                                Cuentas correspondientes al semestre seleccionado.
                            @else
                                Ordenados por fecha de registro, del más reciente al más antiguo.
                            @endif
                        </p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-1 text-[11px] font-bold text-slate-600 ring-1 ring-slate-200">
                        <x-icon name="users" class="h-3.5 w-3.5 text-slate-400" />
                        {{ $usuarios->total() }} {{ $usuarios->total() === 1 ? 'cuenta' : 'cuentas' }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-[10px] font-black uppercase tracking-[0.16em] text-slate-400 sm:px-5">
                                    Persona
                                </th>
                                @if ($categoria === 'estudiante')
                                    <th class="px-4 py-2.5 text-left text-[10px] font-black uppercase tracking-[0.16em] text-slate-400 sm:px-5">
                                        Semestre
                                    </th>
                                    <th class="px-4 py-2.5 text-left text-[10px] font-black uppercase tracking-[0.16em] text-slate-400 sm:px-5">
                                        Generación
                                    </th>
                                @elseif ($categoria === 'docente')
                                    <th class="px-4 py-2.5 text-left text-[10px] font-black uppercase tracking-[0.16em] text-slate-400 sm:px-5">
                                        Cargo
                                    </th>
                                @endif
                                <th class="px-4 py-2.5 text-left text-[10px] font-black uppercase tracking-[0.16em] text-slate-400 sm:px-5">
                                    Estado
                                </th>
                                <th class="px-4 py-2.5 text-right text-[10px] font-black uppercase tracking-[0.16em] text-slate-400 sm:px-5">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($usuarios as $usuario)
                                @php
                                    $perfil = $categoria === 'estudiante' ? $usuario->estudiante : $usuario->docente;
                                    $nombrePerfil = $perfil
                                        ? trim($perfil->nombres.' '.$perfil->apellido_paterno.' '.$perfil->apellido_materno)
                                        : null;
                                    $nombreMostrado = $nombrePerfil ?: $usuario->name;
                                    $iniciales = collect(explode(' ', $nombreMostrado ?: $usuario->username))
                                        ->filter()
                                        ->map(fn ($parte) => mb_substr($parte, 0, 1))
                                        ->take(2)
                                        ->join('');
                                    $estadoPerfil = $perfil?->estado;
                                    $avatarClass = match ($categoria) {
                                        'docente' => 'from-cyan-500 to-blue-500',
                                        'estudiante' => 'from-emerald-500 to-teal-500',
                                        default => 'from-indigo-500 to-violet-500',
                                    };
                                @endphp

                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-4 py-3 sm:px-5">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $avatarClass }} text-xs font-black text-white shadow-sm">
                                                {{ $iniciales ?: '?' }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-slate-800">{{ $nombreMostrado }}</p>
                                                <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ $usuario->username }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    @if ($categoria === 'estudiante')
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-700 ring-1 ring-indigo-100">
                                                {{ $perfil?->semestre ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-xs font-semibold text-slate-600 sm:px-5">
                                            {{ $perfil?->generacion_ingreso ?? '—' }}
                                        </td>
                                    @elseif ($categoria === 'docente')
                                        <td class="whitespace-nowrap px-4 py-3 text-xs font-semibold text-slate-600 sm:px-5">
                                            {{ $perfil?->cargo ?? 'Sin cargo' }}
                                        </td>
                                    @endif

                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                        @if ($estadoPerfil === 'activo')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-100">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Activo
                                            </span>
                                        @elseif ($estadoPerfil === 'inactivo')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 ring-1 ring-rose-100">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                Inactivo
                                            </span>
                                        @elseif ($estadoPerfil === 'licencia')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700 ring-1 ring-amber-100">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                Licencia
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                                                Sin perfil
                                            </span>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                        <a href="{{ route('usuarios.show', $usuario->id) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-[11px] font-bold text-[#272F7A] transition hover:bg-indigo-100">
                                            <x-icon name="eye" class="h-3.5 w-3.5" />
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $categoria === 'administrador' ? 3 : ($categoria === 'docente' ? 4 : 5) }}"
                                        class="px-4 py-10 text-center">
                                        <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                                            <x-icon name="users" class="h-5 w-5" />
                                        </span>
                                        <p class="mt-2 text-sm font-bold text-slate-700">
                                            No se encontraron cuentas
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            @if ($categoria === 'estudiante')
                                                Prueba con otro nombre o revisa el semestre seleccionado.
                                            @else
                                                No hay usuarios registrados en esta categoría.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($usuarios->hasPages())
                    <div class="border-t border-slate-100 px-4 py-3 sm:px-5">
                        {{ $usuarios->withQueryString()->links() }}
                    </div>
                @endif
            </section>

            <p class="mt-3 text-[11px] text-slate-400">
                Las contraseñas y respuestas de seguridad no se muestran en este módulo.
            </p>
        </main>
    </x-admin-shell>
</x-admin-layout>

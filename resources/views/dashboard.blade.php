<x-admin-layout>
    @php
        $stats = $estadisticas ?? [];
        $totalEstudiantes = $stats['estudiantes_activos'] ?? 0;
        $totalDocentes = $stats['docentes_activos'] ?? 0;
        $totalAdministradores = $stats['administradores_activos'] ?? 0;
        $totalInvitados = $stats['invitados_activos'] ?? 0;
        $solicitudesPendientes = $stats['solicitudes_pendientes'] ?? 0;
        $materiasActivas = $stats['materias_activas'] ?? 0;
        $horariosVigentes = $stats['horarios_vigentes'] ?? 0;
        $inscripcionesVigentes = $stats['inscripciones_vigentes'] ?? 0;
        $cuentasBloqueadas = $stats['cuentas_bloqueadas'] ?? 0;
        $totalEquipos = max(0, $stats['total_equipos'] ?? 0);
        $equiposConectados = max(0, $stats['equipos_conectados'] ?? 0);
        $equiposDesconectados = max(0, $stats['equipos_desconectados'] ?? 0);
        $equiposSinDatos = max(0, $stats['equipos_sin_datos'] ?? 0);
        $disponibilidad = $totalEquipos > 0
            ? max(0, min(100, (int) round(($equiposConectados / $totalEquipos) * 100)))
            : 0;
        $gestionCodigo = $gestionVigente?->codigo ?? 'Sin gestión';
        $nombreUsuario = auth()->user()->name;
        $inicial = strtoupper(substr($nombreUsuario, 0, 1));
        $semestres = $estudiantesPorSemestre ?? collect([1 => 0, 2 => 0, 3 => 0, 4 => 0]);
        $maxSemestre = max(1, $maxEstudiantesSemestre ?? 1);
        $saludo = now()->hour < 12 ? 'Buenos días' : (now()->hour < 19 ? 'Buenas tardes' : 'Buenas noches');
    @endphp

    <div class="min-h-screen bg-[#F4F7FC] text-slate-800" x-data="{ sidebarOpen: false, notificationsOpen: false, profileOpen: false, inventoryOpen: false, qrOpen: false, clearNotifications() { fetch('{{ route('admin.notifications.clear') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(() => window.location.reload()).catch(() => {}); } }">
        <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
            <div class="dashboard-orb dashboard-orb-blue absolute -left-48 -top-48 h-[32rem] w-[32rem] rounded-full blur-3xl"></div>
            <div class="dashboard-orb dashboard-orb-cyan absolute -bottom-56 right-0 h-[34rem] w-[34rem] rounded-full blur-3xl"></div>
            <div class="dashboard-grid absolute inset-0 opacity-50"></div>
        </div>

        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

        <aside class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col overflow-hidden bg-gradient-to-b from-[#0B1226] via-[#111C3A] to-[#1E1B4B] text-white shadow-2xl shadow-indigo-950/30 transition-transform duration-300 lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" aria-label="Navegación administrativa">
            <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl" aria-hidden="true"></div>

            <div class="relative shrink-0 border-b border-white/10 px-5 py-5">
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('dashboard') }}" class="group flex items-center gap-3">
                        <span class="relative flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-300 via-blue-400 to-indigo-500 text-sm font-black text-slate-950 shadow-lg shadow-cyan-950/40 transition duration-300 group-hover:rotate-3 group-hover:scale-105">
                            IT
                            <span class="absolute -right-1 -top-1 h-3 w-3 rounded-full border-2 border-[#0B1226] bg-emerald-400"></span>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-black tracking-[0.14em] text-white">ITIBB</span>
                            <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.18em] text-cyan-200/80">Centro de control</span>
                        </span>
                    </a>
                    <button type="button" @click="sidebarOpen = false" class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-indigo-100 transition hover:bg-white/10 lg:hidden" aria-label="Cerrar menú">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-5 flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.9)]"></span>
                    <span class="text-[11px] font-semibold text-indigo-100">Sesión administrativa activa</span>
                </div>
            </div>

            <nav class="dashboard-scrollbar relative flex-1 space-y-6 overflow-y-auto px-3 py-5">
                <div>
                    <p class="mb-2 px-3 text-[10px] font-black uppercase tracking-[0.22em] text-indigo-300/70">Principal</p>
                    <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white shadow-inner shadow-white/5 ring-1 ring-white/10' : 'text-indigo-100/70 hover:bg-white/5 hover:text-white' }}">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                            <x-icon name="home" class="h-4 w-4" />
                        </span>
                        <span>Inicio</span>
                        @if (request()->routeIs('dashboard'))
                            <span class="ml-auto h-1.5 w-1.5 rounded-full bg-cyan-300"></span>
                        @endif
                    </a>
                </div>

                <div>
                    <p class="mb-2 px-3 text-[10px] font-black uppercase tracking-[0.22em] text-indigo-300/70">Actores</p>
                    <div class="space-y-1">
                        <a href="{{ route('usuarios.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('usuarios.*') ? 'bg-white/10 text-white shadow-inner shadow-white/5 ring-1 ring-white/10' : 'text-indigo-100/70 hover:bg-white/5 hover:text-white' }}">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="users" class="h-4 w-4" />
                            </span>
                            <span>Personas y usuarios</span>
                        </a>

                        <a href="{{ route('admin.solicitudes.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.solicitudes.*') ? 'bg-white/10 text-white shadow-inner shadow-white/5 ring-1 ring-white/10' : 'text-indigo-100/70 hover:bg-white/5 hover:text-white' }}">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="clipboard" class="h-4 w-4" />
                            </span>
                            <span>Solicitudes de registro</span>
                            @if ($solicitudesPendientes > 0)
                                <span class="ml-auto rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm">{{ $solicitudesPendientes > 99 ? '99+' : $solicitudesPendientes }}</span>
                            @endif
                        </a>

                        <a href="{{ route('invitados.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('invitados.*') ? 'bg-white/10 text-white shadow-inner shadow-white/5 ring-1 ring-white/10' : 'text-indigo-100/70 hover:bg-white/5 hover:text-white' }}">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="user-plus" class="h-4 w-4" />
                            </span>
                            <span>Invitados</span>
                        </a>

                        <a href="{{ route('seguimiento-estudiantil.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-indigo-300/50 ring-1 ring-white/5">
                                <x-icon name="user-check" class="h-4 w-4" />
                            </span>
                            <span>Seguimiento estudiantil</span>
                        </a>
                    </div>
                </div>

                <div>
                    <p class="mb-2 px-3 text-[10px] font-black uppercase tracking-[0.22em] text-indigo-300/70">Académico</p>
                    <a href="{{ route('gestion-academica.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-indigo-300/50 ring-1 ring-white/5">
                            <x-icon name="academic-cap" class="h-4 w-4" />
                        </span>
                        <span>Gestión académica</span>
                    </a>
                </div>

                <div>
                    <p class="mb-2 px-3 text-[10px] font-black uppercase tracking-[0.22em] text-indigo-300/70">Seguridad</p>
                    <a href="{{ route('admin.bitacora.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.bitacora.*') ? 'bg-white/10 text-white shadow-inner shadow-white/5 ring-1 ring-white/10' : 'text-indigo-100/70 hover:bg-white/5 hover:text-white' }}">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                            <x-icon name="shield" class="h-4 w-4" />
                        </span>
                        <span>Bitácora del sistema</span>
                    </a>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between px-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-indigo-300/70">Operación</p>
                        <span class="rounded-full bg-white/5 px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-indigo-300/70 ring-1 ring-white/10">Solo lectura</span>
                    </div>
                    <div class="space-y-1">
                        <button type="button" @click="inventoryOpen = !inventoryOpen" :aria-expanded="inventoryOpen" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="inventory" class="h-4 w-4" />
                            </span>
                            <span>Inventario</span>
                            <span class="ml-auto transition duration-200" :class="inventoryOpen ? 'rotate-180' : ''">
                                <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                            </span>
                        </button>
                        <div x-cloak x-show="inventoryOpen" x-transition class="ml-6 space-y-1 border-l border-white/10 pl-3">
                            <a href="{{ route('equipos.index') }}" class="block rounded-lg px-3 py-2 text-xs font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">Equipos</a>
                            <a href="{{ route('accesorios.index') }}" class="block rounded-lg px-3 py-2 text-xs font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">Accesorios</a>
                        </div>

                        <a href="{{ route('asignaciones.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="assignment" class="h-4 w-4" />
                            </span>
                            <span>Préstamos y asignaciones</span>
                        </a>
                        <a href="{{ route('incidencias.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="incident" class="h-4 w-4" />
                            </span>
                            <span>Incidencias</span>
                        </a>
                        <a href="{{ route('monitoreo.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="monitor" class="h-4 w-4" />
                            </span>
                            <span>Monitoreo</span>
                        </a>
                        <a href="{{ route('diagnosticos.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="diagnostic" class="h-4 w-4" />
                            </span>
                            <span>Diagnósticos</span>
                        </a>
                        <a href="{{ route('reportes.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="report" class="h-4 w-4" />
                            </span>
                            <span>Reportes</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>

        <div class="relative min-h-screen lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/90 px-4 py-3 backdrop-blur-xl sm:px-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" @click="sidebarOpen = true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-indigo-700 shadow-sm transition hover:border-indigo-200 lg:hidden" aria-label="Abrir menú">
                            <x-icon name="menu" class="h-5 w-5" />
                        </button>
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-indigo-500">Panel administrativo</p>
                            <h1 class="truncate text-xl font-black tracking-tight text-slate-900 sm:text-2xl">Centro de control ITIBB</h1>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                        <div class="relative" @click.outside="notificationsOpen = false">
                            <button type="button" @click="notificationsOpen = !notificationsOpen" :aria-expanded="notificationsOpen" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-indigo-200 hover:text-indigo-700" aria-label="Notificaciones">
                                <x-icon name="bell" class="h-5 w-5" />
                                @if (($notificacionesNoLeidas ?? 0) > 0)
                                    <span class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-black text-white ring-2 ring-white">{{ $notificacionesNoLeidas > 9 ? '9+' : $notificacionesNoLeidas }}</span>
                                @endif
                            </button>

                            <div x-cloak x-show="notificationsOpen" x-transition class="absolute right-0 top-12 z-50 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20">
                                <div class="border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-cyan-50/60 px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-black text-slate-800">Notificaciones</p>
                                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm ring-1 ring-indigo-100">
                                            <x-icon name="bell" class="h-4 w-4" />
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-[11px] text-slate-500">Actividad reciente del sistema</p>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('admin.bitacora.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg bg-white px-2.5 py-2 text-[11px] font-bold text-indigo-700 shadow-sm ring-1 ring-indigo-100 transition hover:bg-indigo-600 hover:text-white">
                                            Ver bitácora
                                        </a>
                                        <button type="button" @click="clearNotifications()" class="inline-flex flex-1 items-center justify-center rounded-lg bg-white px-2.5 py-2 text-[11px] font-bold text-rose-600 shadow-sm ring-1 ring-rose-100 transition hover:bg-rose-600 hover:text-white">
                                            Limpiar bandeja
                                        </button>
                                    </div>
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    @forelse (($notificaciones ?? collect()) as $notificacion)
                                        <a href="{{ $notificacion->url ?: route('admin.bitacora.index') }}" class="group flex gap-3 border-b border-slate-50 px-4 py-3 transition hover:bg-indigo-50/70">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100 transition group-hover:bg-white">
                                                <x-icon name="bell" class="h-4 w-4" />
                                            </span>
                                            <span class="min-w-0">
                                                <p class="truncate text-xs font-bold text-slate-800">{{ $notificacion->titulo }}</p>
                                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ $notificacion->mensaje }}</p>
                                            </span>
                                        </a>
                                    @empty
                                        <div class="px-4 py-8 text-center">
                                            <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                                                <x-icon name="bell" class="h-5 w-5" />
                                            </span>
                                            <p class="mt-2 text-xs font-semibold text-slate-500">No hay notificaciones nuevas</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="qrOpen = true"
                            class="group flex h-10 items-center justify-center gap-2 rounded-xl border border-cyan-100 bg-white px-3 text-sm font-bold text-cyan-700 shadow-sm transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-800"
                            aria-label="Abrir acceso estudiantil QR"
                            title="Acceso estudiantil QR"
                        >
                            <x-icon name="qr-code" class="h-4 w-4 transition group-hover:scale-110" />
                            <span class="hidden lg:inline">Acceso estudiantil QR</span>
                        </button>

                        <div class="relative" @click.outside="profileOpen = false">
                            <button type="button" @click="profileOpen = !profileOpen" :aria-expanded="profileOpen" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-2.5 shadow-sm transition hover:border-indigo-200">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-cyan-400 text-sm font-black text-white">{{ $inicial }}</span>
                                <span class="hidden text-left sm:block">
                                    <span class="block max-w-[9rem] truncate text-xs font-bold text-slate-800">{{ $nombreUsuario }}</span>
                                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">Administrador</span>
                                </span>
                                <x-icon name="chevron-down" class="hidden h-3.5 w-3.5 text-slate-400 sm:block" />
                            </button>

                            <div x-cloak x-show="profileOpen" x-transition class="absolute right-0 top-12 z-50 w-60 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl shadow-slate-900/20">
                                <div class="border-b border-slate-100 px-3 py-2">
                                    <p class="truncate text-xs font-bold text-slate-800">{{ $nombreUsuario }}</p>
                                    <p class="mt-0.5 truncate text-[11px] text-slate-500">{{ auth()->user()->username }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="mt-1 flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-indigo-50 hover:text-indigo-700">
                                    <x-icon name="cog" class="h-4 w-4" />
                                    Mi cuenta
                                </a>
                                <a href="{{ route('security.questions.edit') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-indigo-50 hover:text-indigo-700">
                                    <x-icon name="key" class="h-4 w-4" />
                                    Preguntas de seguridad
                                </a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex h-10 items-center justify-center gap-2 rounded-xl border border-rose-100 bg-white px-3 text-sm font-bold text-rose-600 shadow-sm transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700" aria-label="Cerrar sesión" title="Cerrar sesión">
                                <x-icon name="logout" class="h-4 w-4" />
                                <span class="hidden sm:inline">Cerrar sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <div
                x-cloak
                x-show="qrOpen"
                x-transition.opacity
                @keydown.escape.window="qrOpen = false"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="admin-qr-title"
                @click.self="qrOpen = false"
            >
                <div x-show="qrOpen" x-transition class="relative flex h-[min(90vh,850px)] w-full max-w-2xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
                    <div class="flex shrink-0 items-center justify-between gap-4 bg-gradient-to-r from-slate-950 via-blue-950 to-indigo-950 px-5 py-4 text-white sm:px-7">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.25em] text-cyan-300">Portal del estudiante</p>
                            <h2 id="admin-qr-title" class="mt-1 text-lg font-black sm:text-xl">Acceso estudiantil QR</h2>
                        </div>
                        <button type="button" @click="qrOpen = false" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-slate-300 transition hover:bg-white/10 hover:text-white" aria-label="Cerrar acceso estudiantil QR">
                            <x-icon name="x" class="h-5 w-5" />
                        </button>
                    </div>
                    <iframe src="{{ route('estudiante.qr', ['embed' => 1]) }}" title="Código QR de acceso al portal estudiantil" class="min-h-0 flex-1 w-full border-0 bg-slate-950"></iframe>
                </div>
            </div>

            <main class="mx-auto max-w-[1600px] space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <section class="dashboard-rise relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0B1226] via-[#1E2A5A] to-[#272F7A] px-6 py-7 text-white shadow-2xl shadow-indigo-950/20 sm:px-8 sm:py-8">
                    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -bottom-28 left-1/3 h-64 w-64 rounded-full bg-indigo-400/20 blur-3xl" aria-hidden="true"></div>
                    <div class="dashboard-grid pointer-events-none absolute inset-0 opacity-30" aria-hidden="true"></div>

                    <div class="relative flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                        <div class="max-w-2xl">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em] text-cyan-100">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300 shadow-[0_0_10px_rgba(110,231,183,0.9)]"></span>
                                    Sistema operativo
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-indigo-100">
                                    <x-icon name="calendar" class="h-3.5 w-3.5 text-cyan-200" />
                                    {{ $gestionVigente ? 'Gestión '.$gestionCodigo : 'Sin gestión vigente' }}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-indigo-100">
                                    <x-icon name="clock" class="h-3.5 w-3.5 text-cyan-200" />
                                    {{ now()->format('d/m/Y · H:i') }}
                                </span>
                            </div>

                            <h2 class="mt-5 text-3xl font-black tracking-tight sm:text-4xl">{{ $saludo }}, {{ $nombreUsuario }}.</h2>
                            <p class="mt-3 text-sm leading-6 text-indigo-100/80">Tu centro de control para actores, gestión académica y seguridad. Todo lo importante, en un solo vistazo.</p>
                        </div>

                    </div>
                </section>

                <section>
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-indigo-500">Vista general</p>
                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-900">Resumen del Incremento 1</h2>
                        </div>
                        <span class="hidden items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-100 sm:inline-flex">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Datos actualizados
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <a href="{{ route('usuarios.index', ['categoria' => 'estudiante']) }}" class="dashboard-card dashboard-rise group relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-[0_18px_45px_-30px_rgba(15,23,42,0.35)] transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-xl hover:shadow-indigo-900/10">
                            <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-indigo-50 blur-2xl transition group-hover:bg-indigo-100" aria-hidden="true"></div>
                            <div class="relative flex items-start justify-between gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100 transition group-hover:scale-105">
                                    <x-icon name="users" class="h-5 w-5" />
                                </span>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-100">Activos</span>
                            </div>
                            <p class="relative mt-5 text-3xl font-black tracking-tight text-slate-900">{{ number_format($totalEstudiantes) }}</p>
                            <p class="relative mt-1 text-sm font-semibold text-slate-500">Estudiantes activos</p>
                            <div class="relative mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] font-semibold text-slate-400">
                                <span>1.º a 4.º semestre</span>
                                <span class="inline-flex items-center gap-1 text-indigo-600 transition group-hover:gap-2">Ver <x-icon name="arrow-right" class="h-3 w-3" /></span>
                            </div>
                        </a>

                        <a href="{{ route('usuarios.index', ['categoria' => 'docente']) }}" class="dashboard-card dashboard-rise dashboard-rise-delay-1 group relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-[0_18px_45px_-30px_rgba(15,23,42,0.35)] transition duration-300 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-xl hover:shadow-cyan-900/10">
                            <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-cyan-50 blur-2xl transition group-hover:bg-cyan-100" aria-hidden="true"></div>
                            <div class="relative flex items-start justify-between gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100 transition group-hover:scale-105">
                                    <x-icon name="academic-cap" class="h-5 w-5" />
                                </span>
                                <span class="rounded-full bg-cyan-50 px-2.5 py-1 text-[10px] font-bold text-cyan-700 ring-1 ring-cyan-100">Plantel</span>
                            </div>
                            <p class="relative mt-5 text-3xl font-black tracking-tight text-slate-900">{{ number_format($totalDocentes) }}</p>
                            <p class="relative mt-1 text-sm font-semibold text-slate-500">Docentes activos</p>
                            <div class="relative mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] font-semibold text-slate-400">
                                <span>Personal académico</span>
                                <span class="inline-flex items-center gap-1 text-cyan-600 transition group-hover:gap-2">Ver <x-icon name="arrow-right" class="h-3 w-3" /></span>
                            </div>
                        </a>

                        <a href="{{ route('admin.solicitudes.index', ['estado' => 'pendiente']) }}" class="dashboard-card dashboard-rise dashboard-rise-delay-2 group relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-[0_18px_45px_-30px_rgba(15,23,42,0.35)] transition duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-xl hover:shadow-amber-900/10">
                            <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-amber-50 blur-2xl transition group-hover:bg-amber-100" aria-hidden="true"></div>
                            <div class="relative flex items-start justify-between gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-700 ring-1 ring-amber-100 transition group-hover:scale-105">
                                    <x-icon name="clipboard" class="h-5 w-5" />
                                </span>
                                @if ($solicitudesPendientes > 0)
                                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-700 ring-1 ring-rose-100">Requieren revisión</span>
                                @else
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-100">Al día</span>
                                @endif
                            </div>
                            <p class="relative mt-5 text-3xl font-black tracking-tight text-slate-900">{{ number_format($solicitudesPendientes) }}</p>
                            <p class="relative mt-1 text-sm font-semibold text-slate-500">Solicitudes pendientes</p>
                            <div class="relative mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] font-semibold text-slate-400">
                                <span>Registro de nuevos actores</span>
                                <span class="inline-flex items-center gap-1 text-amber-600 transition group-hover:gap-2">Revisar <x-icon name="arrow-right" class="h-3 w-3" /></span>
                            </div>
                        </a>

                        <div class="dashboard-card dashboard-rise dashboard-rise-delay-3 group relative overflow-hidden rounded-2xl border border-slate-200/70 bg-white p-5 shadow-[0_18px_45px_-30px_rgba(15,23,42,0.35)] transition duration-300 hover:-translate-y-1 hover:border-violet-200 hover:shadow-xl hover:shadow-violet-900/10">
                            <div class="pointer-events-none absolute -right-8 -top-8 h-24 w-24 rounded-full bg-violet-50 blur-2xl transition group-hover:bg-violet-100" aria-hidden="true"></div>
                            <div class="relative flex items-start justify-between gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-700 ring-1 ring-violet-100 transition group-hover:scale-105">
                                    <x-icon name="chart" class="h-5 w-5" />
                                </span>
                                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-bold text-violet-700 ring-1 ring-violet-100">{{ $gestionVigente ? 'Gestión '.$gestionCodigo : 'Sin gestión' }}</span>
                            </div>
                            <p class="relative mt-5 text-3xl font-black tracking-tight text-slate-900">{{ number_format($inscripcionesVigentes) }}</p>
                            <p class="relative mt-1 text-sm font-semibold text-slate-500">Inscripciones confirmadas</p>
                            <div class="relative mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] font-semibold text-slate-400">
                                <span>Periodo académico vigente</span>
                                <span class="text-violet-600">Académico</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.45fr_0.75fr]">
                    <div class="space-y-6">
                        <article class="dashboard-card overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)]">
                            <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500">Académico</p>
                                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Población estudiantil por semestre</h2>
                                </div>
                                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-[11px] font-bold text-indigo-700 ring-1 ring-indigo-100">
                                    <x-icon name="chart" class="h-3.5 w-3.5" />
                                    {{ number_format($totalEstudiantes) }} activos
                                </span>
                            </div>

                            <div class="p-6">
                                <div class="flex items-end gap-4 sm:gap-6">
                                    @foreach ($semestres as $semestre => $total)
                                        @php
                                            $porcentaje = max(8, (int) round(($total / $maxSemestre) * 100));
                                        @endphp
                                        <div class="flex flex-1 flex-col items-center gap-2">
                                            <span class="text-xs font-black text-slate-700">{{ $total }}</span>
                                            <div class="flex h-36 w-full items-end rounded-xl bg-slate-50 p-1 ring-1 ring-slate-100" title="{{ $total }} estudiantes en {{ $semestre }}.º semestre">
                                                <div class="w-full rounded-lg bg-gradient-to-t from-indigo-600 via-indigo-500 to-cyan-400 shadow-[0_10px_20px_-12px_rgba(79,70,229,0.8)] transition-all duration-700" style="height: {{ $porcentaje }}%"></div>
                                            </div>
                                            <span class="text-[11px] font-semibold text-slate-500">{{ $semestre }}.º</span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
                                        <div class="flex items-center gap-2 text-slate-400">
                                            <x-icon name="book" class="h-3.5 w-3.5" />
                                            <p class="text-[10px] font-bold uppercase tracking-wider">Materias</p>
                                        </div>
                                        <p class="mt-1 text-xl font-black text-slate-800">{{ number_format($materiasActivas) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
                                        <div class="flex items-center gap-2 text-slate-400">
                                            <x-icon name="calendar" class="h-3.5 w-3.5" />
                                            <p class="text-[10px] font-bold uppercase tracking-wider">Horarios</p>
                                        </div>
                                        <p class="mt-1 text-xl font-black text-slate-800">{{ number_format($horariosVigentes) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100">
                                        <div class="flex items-center gap-2 text-slate-400">
                                            <x-icon name="document" class="h-3.5 w-3.5" />
                                            <p class="text-[10px] font-bold uppercase tracking-wider">Inscripciones</p>
                                        </div>
                                        <p class="mt-1 text-xl font-black text-slate-800">{{ number_format($inscripcionesVigentes) }}</p>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <article class="dashboard-card overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)]">
                            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500">Auditoría</p>
                                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Actividad reciente</h2>
                                </div>
                                <a href="{{ route('admin.bitacora.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 transition hover:gap-2 hover:text-indigo-800">
                                    Ver bitácora
                                    <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                </a>
                            </div>

                            <div class="px-6 py-5">
                                @forelse ($bitacoraReciente as $evento)
                                    @php
                                        $accion = strtolower((string) $evento->accion);
                                        $icono = match (true) {
                                            str_contains($accion, 'solicitud') => 'clipboard',
                                            str_contains($accion, 'usuario') || str_contains($accion, 'estudiante') || str_contains($accion, 'docente') => 'users',
                                            str_contains($accion, 'materia') || str_contains($accion, 'horario') || str_contains($accion, 'inscrip') => 'academic-cap',
                                            str_contains($accion, 'incidencia') => 'warning',
                                            str_contains($accion, 'pregunta') || str_contains($accion, 'password') => 'lock',
                                            default => 'activity',
                                        };
                                        $color = match ($icono) {
                                            'clipboard' => 'bg-amber-50 text-amber-600 ring-amber-100',
                                            'users' => 'bg-indigo-50 text-indigo-600 ring-indigo-100',
                                            'academic-cap' => 'bg-cyan-50 text-cyan-600 ring-cyan-100',
                                            'warning' => 'bg-rose-50 text-rose-600 ring-rose-100',
                                            'lock' => 'bg-slate-100 text-slate-600 ring-slate-200',
                                            default => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
                                        };
                                    @endphp
                                    <div class="relative flex gap-4 pb-5 last:pb-0">
                                        @unless ($loop->last)
                                            <span class="absolute left-4 top-9 h-[calc(100%-1.5rem)] w-px bg-slate-200" aria-hidden="true"></span>
                                        @endunless
                                        <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl ring-1 {{ $color }}">
                                            <x-icon :name="$icono" class="h-4 w-4" />
                                        </span>
                                        <div class="min-w-0 flex-1 pt-0.5">
                                            <p class="truncate text-sm font-bold text-slate-800">{{ ucfirst(str_replace('_', ' ', $evento->accion)) }}</p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $evento->user?->username ?? 'Sistema' }}
                                                @if ($evento->entidad)
                                                    · {{ $evento->entidad }}
                                                @endif
                                                · {{ $evento->created_at?->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-8 text-center">
                                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                                            <x-icon name="activity" class="h-6 w-6" />
                                        </span>
                                        <p class="mt-3 text-sm font-bold text-slate-600">Sin actividad registrada</p>
                                        <p class="mt-1 text-xs text-slate-400">Los eventos del sistema aparecerán aquí.</p>
                                    </div>
                                @endforelse
                            </div>
                        </article>
                    </div>

                    <div class="space-y-6">
                        <article class="dashboard-card overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)]">
                            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500">Actores</p>
                                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Solicitudes pendientes</h2>
                                </div>
                                <span class="flex h-8 min-w-[2rem] items-center justify-center rounded-full bg-amber-50 px-2 text-xs font-black text-amber-700 ring-1 ring-amber-100">{{ $solicitudesPendientes }}</span>
                            </div>

                            <div class="p-3">
                                @forelse ($solicitudesRecientes as $solicitud)
                                    <a href="{{ route('admin.solicitudes.index', ['estado' => 'pendiente']) }}" class="group flex items-center gap-3 rounded-2xl px-3 py-3 transition hover:bg-amber-50/60">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100 text-sm font-black text-amber-700 ring-1 ring-amber-200/60">
                                            {{ strtoupper(substr($solicitud->nombres, 0, 1)) }}{{ strtoupper(substr($solicitud->apellido_paterno, 0, 1)) }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold text-slate-800">{{ $solicitud->nombres }} {{ $solicitud->apellido_paterno }}</p>
                                            <p class="mt-0.5 text-[11px] text-slate-500">CI {{ $solicitud->ci }} · {{ $solicitud->created_at?->diffForHumans() }}</p>
                                        </div>
                                        <span class="rounded-full bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700 ring-1 ring-amber-100">Pendiente</span>
                                    </a>
                                @empty
                                    <div class="px-3 py-8 text-center">
                                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500 ring-1 ring-emerald-100">
                                            <x-icon name="check" class="h-6 w-6" />
                                        </span>
                                        <p class="mt-3 text-sm font-bold text-slate-600">Todo al día</p>
                                        <p class="mt-1 text-xs text-slate-400">No hay solicitudes pendientes.</p>
                                    </div>
                                @endforelse
                            </div>

                            <div class="border-t border-slate-100 px-5 py-3">
                                <a href="{{ route('admin.solicitudes.index') }}" class="flex items-center justify-between text-xs font-bold text-indigo-600 transition hover:text-indigo-800">
                                    Ver todas las solicitudes
                                    <x-icon name="arrow-right" class="h-4 w-4" />
                                </a>
                            </div>
                        </article>

                        <article class="dashboard-card overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)]">
                            <div class="flex items-start justify-between border-b border-slate-100 px-5 py-5">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-500">Operación</p>
                                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Estado del sistema</h2>
                                </div>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-slate-500 ring-1 ring-slate-200">Solo lectura</span>
                            </div>

                            <div class="space-y-4 p-5">
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-xl bg-emerald-50 px-3 py-3 text-center ring-1 ring-emerald-100">
                                        <p class="text-xl font-black text-emerald-700">{{ $equiposConectados }}</p>
                                        <p class="mt-1 text-[9px] font-bold uppercase tracking-wider text-emerald-600">Conectados</p>
                                    </div>
                                    <div class="rounded-xl bg-rose-50 px-3 py-3 text-center ring-1 ring-rose-100">
                                        <p class="text-xl font-black text-rose-700">{{ $equiposDesconectados }}</p>
                                        <p class="mt-1 text-[9px] font-bold uppercase tracking-wider text-rose-600">Offline</p>
                                    </div>
                                    <div class="rounded-xl bg-slate-100 px-3 py-3 text-center ring-1 ring-slate-200">
                                        <p class="text-xl font-black text-slate-700">{{ $equiposSinDatos }}</p>
                                        <p class="mt-1 text-[9px] font-bold uppercase tracking-wider text-slate-500">Sin datos</p>
                                    </div>
                                </div>

                                <div>
                                    <div class="mb-2 flex items-center justify-between text-[11px] font-semibold text-slate-500">
                                        <span>Disponibilidad operativa</span>
                                        <span class="font-black text-slate-700">{{ $disponibilidad }}%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="dashboard-progress h-full rounded-full bg-gradient-to-r from-emerald-400 to-cyan-400" style="width: {{ $disponibilidad }}%"></div>
                                    </div>
                                </div>

                                <a href="{{ route('monitoreo.index') }}" class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5 text-xs font-bold text-slate-600 ring-1 ring-slate-100 transition hover:bg-indigo-50 hover:text-indigo-700">
                                    Abrir monitoreo
                                    <x-icon name="arrow-right" class="h-4 w-4" />
                                </a>
                            </div>
                        </article>

                        <article class="dashboard-card overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)]">
                            <div class="border-b border-slate-100 px-5 py-5">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500">Accesos</p>
                                <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Acciones rápidas</h2>
                            </div>
                            <div class="grid grid-cols-2 gap-2 p-3">
                                <a href="{{ route('usuarios.create') }}" class="group rounded-2xl border border-slate-200/70 bg-slate-50/60 p-3 transition hover:border-indigo-200 hover:bg-indigo-50">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200/60 transition group-hover:scale-105">
                                        <x-icon name="user-plus" class="h-4 w-4" />
                                    </span>
                                    <p class="mt-2 text-xs font-bold text-slate-700 group-hover:text-indigo-800">Nuevo usuario</p>
                                </a>
                                <a href="{{ route('admin.solicitudes.index') }}" class="group rounded-2xl border border-slate-200/70 bg-slate-50/60 p-3 transition hover:border-amber-200 hover:bg-amber-50">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 ring-1 ring-amber-200/60 transition group-hover:scale-105">
                                        <x-icon name="clipboard" class="h-4 w-4" />
                                    </span>
                                    <p class="mt-2 text-xs font-bold text-slate-700 group-hover:text-amber-800">Solicitudes</p>
                                </a>
                                <a href="{{ route('admin.bitacora.index') }}" class="group rounded-2xl border border-slate-200/70 bg-slate-50/60 p-3 transition hover:border-cyan-200 hover:bg-cyan-50">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700 ring-1 ring-cyan-200/60 transition group-hover:scale-105">
                                        <x-icon name="shield" class="h-4 w-4" />
                                    </span>
                                    <p class="mt-2 text-xs font-bold text-slate-700 group-hover:text-cyan-800">Bitácora</p>
                                </a>
                                <a href="{{ route('monitoreo.index') }}" class="group rounded-2xl border border-slate-200/70 bg-slate-50/60 p-3 transition hover:border-emerald-200 hover:bg-emerald-50">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200/60 transition group-hover:scale-105">
                                        <x-icon name="monitor" class="h-4 w-4" />
                                    </span>
                                    <p class="mt-2 text-xs font-bold text-slate-700 group-hover:text-emerald-800">Monitoreo</p>
                                </a>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-2">
                    <article class="dashboard-card overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)]">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500">Operación</p>
                                <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Asignaciones recientes</h2>
                            </div>
                            <a href="{{ route('asignaciones.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 transition hover:gap-2 hover:text-indigo-800">
                                Ver todas
                                <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                            </a>
                        </div>
                        <div class="divide-y divide-slate-100 px-6">
                            @forelse (($asignacionesRecientes ?? collect()) as $asignacion)
                                <div class="flex items-center gap-3 py-4">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                                        <x-icon name="monitor" class="h-5 w-5" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold text-slate-800">{{ $asignacion->equipo->codigo ?? 'Equipo sin código' }}</p>
                                        <p class="mt-1 truncate text-xs text-slate-500">{{ $asignacion->estudiante->nombres ?? 'Estudiante no disponible' }} {{ $asignacion->estudiante->apellido_paterno ?? '' }}</p>
                                    </div>
                                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-bold text-indigo-700 ring-1 ring-indigo-100">{{ ucfirst(strtolower($asignacion->estado ?? 'Registrada')) }}</span>
                                </div>
                            @empty
                                <div class="py-10 text-center">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                                        <x-icon name="assignment" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-bold text-slate-600">Sin asignaciones recientes</p>
                                    <p class="mt-1 text-xs text-slate-400">La actividad aparecerá aquí.</p>
                                </div>
                            @endforelse
                        </div>
                    </article>

                    <article class="dashboard-card overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)]">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-500">Atención prioritaria</p>
                                <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Diagnósticos críticos</h2>
                            </div>
                            <a href="{{ route('diagnosticos.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 transition hover:gap-2 hover:text-rose-800">
                                Ver todos
                                <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                            </a>
                        </div>
                        <div class="divide-y divide-slate-100 px-6">
                            @forelse (($diagnosticosCriticos ?? collect()) as $diagnostico)
                                <div class="flex items-start gap-3 py-4">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 ring-1 ring-rose-100">
                                        <x-icon name="warning" class="h-5 w-5" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="truncate text-sm font-bold text-slate-800">{{ $diagnostico->equipo->codigo ?? 'Equipo sin código' }}</p>
                                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-rose-700 ring-1 ring-rose-100">{{ $diagnostico->nivel }}</span>
                                        </div>
                                        <p class="mt-1 max-h-10 overflow-hidden text-xs leading-5 text-slate-500">{{ $diagnostico->descripcion }}</p>
                                        <p class="mt-1 text-[10px] text-slate-400">{{ $diagnostico->fecha_hora?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="py-10 text-center">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500 ring-1 ring-emerald-100">
                                        <x-icon name="check" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-bold text-slate-600">Sin diagnósticos críticos</p>
                                    <p class="mt-1 text-xs text-slate-400">Los equipos están operando con normalidad.</p>
                                </div>
                            @endforelse
                        </div>
                    </article>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.45fr_0.75fr]">
                    <article class="dashboard-card overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)]">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500">Seguridad</p>
                                <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Últimos accesos</h2>
                            </div>
                            <a href="{{ route('usuarios.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 transition hover:gap-2 hover:text-indigo-800">
                                Ver usuarios
                                <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                            </a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px] text-left">
                                <thead class="bg-slate-50/80">
                                    <tr>
                                        <th class="px-6 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Usuario</th>
                                        <th class="px-6 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Rol</th>
                                        <th class="px-6 py-3 text-[10px] font-black uppercase tracking-wider text-slate-400">Último acceso</th>
                                        <th class="px-6 py-3 text-right text-[10px] font-black uppercase tracking-wider text-slate-400">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($ultimosAccesos as $usuario)
                                        <tr class="transition hover:bg-indigo-50/40">
                                            <td class="px-6 py-3">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-xs font-black text-indigo-700 ring-1 ring-indigo-100">{{ strtoupper(substr($usuario->nombreCompleto(), 0, 1)) }}</span>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-bold text-slate-800">{{ $usuario->nombreCompleto() }}</p>
                                                        <p class="text-[11px] text-slate-400">{{ '@'.$usuario->username }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-3 text-xs font-semibold capitalize text-slate-600">{{ $usuario->tipo_usuario }}</td>
                                            <td class="px-6 py-3 text-xs text-slate-500">{{ $usuario->ultimo_acceso?->diffForHumans() ?? 'Sin acceso' }}</td>
                                            <td class="px-6 py-3 text-right">
                                                @if ($usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isFuture())
                                                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-700 ring-1 ring-rose-100">Bloqueado</span>
                                                @elseif ($usuario->activo)
                                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-100">Activo</span>
                                                @else
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 ring-1 ring-slate-200">Inactivo</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">Aún no hay accesos registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="dashboard-card overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-[0_20px_60px_-35px_rgba(15,23,42,0.35)]">
                        <div class="border-b border-slate-100 px-5 py-5">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500">Comunidad</p>
                            <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Resumen de actores</h2>
                        </div>

                        <div class="space-y-3 p-5">
                            @foreach ([
                                ['label' => 'Administradores', 'value' => $totalAdministradores, 'icon' => 'shield', 'color' => 'bg-violet-50 text-violet-600 ring-violet-100'],
                                ['label' => 'Docentes', 'value' => $totalDocentes, 'icon' => 'academic-cap', 'color' => 'bg-cyan-50 text-cyan-600 ring-cyan-100'],
                                ['label' => 'Estudiantes', 'value' => $totalEstudiantes, 'icon' => 'users', 'color' => 'bg-indigo-50 text-indigo-600 ring-indigo-100'],
                                ['label' => 'Invitados', 'value' => $totalInvitados, 'icon' => 'user-plus', 'color' => 'bg-amber-50 text-amber-600 ring-amber-100'],
                            ] as $actor)
                                <div class="flex items-center gap-3 rounded-2xl bg-slate-50/70 px-3 py-3 ring-1 ring-slate-100">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ring-1 {{ $actor['color'] }}">
                                        <x-icon :name="$actor['icon']" class="h-4 w-4" />
                                    </span>
                                    <p class="flex-1 text-xs font-bold text-slate-600">{{ $actor['label'] }}</p>
                                    <p class="text-lg font-black text-slate-800">{{ number_format($actor['value']) }}</p>
                                </div>
                            @endforeach

                            <div class="flex items-center justify-between rounded-2xl bg-rose-50/70 px-3 py-3 ring-1 ring-rose-100">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 ring-1 ring-rose-200">
                                        <x-icon name="lock" class="h-4 w-4" />
                                    </span>
                                    <p class="text-xs font-bold text-rose-700">Cuentas bloqueadas</p>
                                </div>
                                <p class="text-lg font-black text-rose-700">{{ number_format($cuentasBloqueadas) }}</p>
                            </div>
                        </div>
                    </article>
                </section>

                <footer class="flex flex-col items-center justify-between gap-2 border-t border-slate-200/80 pt-5 text-center text-[11px] text-slate-400 sm:flex-row sm:text-left">
                    <span>Sistema de Gestión, Monitoreo y Diagnóstico de Equipos de Cómputo</span>
                    <span class="inline-flex items-center gap-2">
                        <x-icon name="sparkles" class="h-3.5 w-3.5 text-indigo-400" />
                        ITIBB · Informática Industrial · Incremento 1
                    </span>
                </footer>
            </main>
        </div>
    </div>
</x-admin-layout>

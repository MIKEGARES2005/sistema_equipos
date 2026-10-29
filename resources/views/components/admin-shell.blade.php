@props([
    'solicitudesPendientes' => null,
    'notificaciones' => null,
    'notificacionesNoLeidas' => null,
])

@php
    $nombreUsuario = auth()->user()?->name ?? '';
    $inicial = strtoupper(substr($nombreUsuario, 0, 1));
    $solicitudesPendientes ??= \App\Models\SolicitudRegistro::query()->where('estado', 'pendiente')->count();
    $notificaciones ??= \App\Models\Notificacion::query()->where('user_id', auth()->id())->latest('created_at')->take(5)->get();
    $notificacionesNoLeidas ??= \App\Models\Notificacion::query()->where('user_id', auth()->id())->whereNull('leida_en')->count();
    $notificacionesIniciales = $notificaciones->map(fn ($notificacion): array => [
        'id' => $notificacion->id,
        'titulo' => $notificacion->titulo,
        'mensaje' => $notificacion->mensaje,
        'url' => $notificacion->url
            ?: ($notificacion->tipo === 'solicitud'
                ? route('admin.solicitudes.index', ['estado' => 'pendiente'])
                : route('admin.bitacora.index')),
        'creada' => $notificacion->created_at?->diffForHumans(),
    ])->values();
@endphp
    <div
        class="min-h-screen bg-[#F4F7FC] text-slate-800"
        x-data="{
            sidebarOpen: false,
            notificationsOpen: false,
            profileOpen: false,
            inventoryOpen: false,
            qrOpen: false,
            notifications: @js($notificacionesIniciales),
            unreadNotifications: {{ $notificacionesNoLeidas }},
            refreshNotifications() {
                fetch('{{ route('admin.notifications.index') }}', { headers: { 'Accept': 'application/json' } })
                    .then(response => response.ok ? response.json() : null)
                    .then(data => {
                        if (data) {
                            this.notifications = data.notificaciones;
                            this.unreadNotifications = data.no_leidas;
                        }
                    })
                    .catch(() => {});
            },
            clearNotifications() {
                fetch('{{ route('admin.notifications.clear') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.ok ? response.json() : null)
                    .then(data => {
                        if (data) {
                            this.notifications = data.notificaciones;
                            this.unreadNotifications = data.no_leidas;
                        }
                    })
                    .catch(() => {});
            }
        }"
        x-init="refreshNotifications(); setInterval(() => refreshNotifications(), 15000)"
    >
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

                        @if (\Illuminate\Support\Facades\Route::has('admin.solicitudes.index'))
                            <a href="{{ route('admin.solicitudes.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.solicitudes.*') ? 'bg-white/10 text-white shadow-inner shadow-white/5 ring-1 ring-white/10' : 'text-indigo-100/70 hover:bg-white/5 hover:text-white' }}">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                    <x-icon name="clipboard" class="h-4 w-4" />
                                </span>
                                <span>Solicitudes de registro</span>
                                @if ($solicitudesPendientes > 0)
                                    <span class="ml-auto rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white shadow-sm">{{ $solicitudesPendientes > 99 ? '99+' : $solicitudesPendientes }}</span>
                                @endif
                            </a>
                        @endif

                        <a href="{{ route('invitados.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('invitados.*') ? 'bg-white/10 text-white shadow-inner shadow-white/5 ring-1 ring-white/10' : 'text-indigo-100/70 hover:bg-white/5 hover:text-white' }}">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="user-plus" class="h-4 w-4" />
                            </span>
                            <span>Invitados</span>
                        </a>

                        <a href="{{ route('seguimiento-estudiantil.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('seguimiento-estudiantil.*') ? 'bg-white/10 text-white shadow-inner shadow-white/5 ring-1 ring-white/10' : 'text-indigo-100/70 hover:bg-white/5 hover:text-white' }}">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-indigo-300/50 ring-1 ring-white/5">
                                <x-icon name="user-check" class="h-4 w-4" />
                            </span>
                            <span>Seguimiento estudiantil</span>
                        </a>
                    </div>
                </div>

                <div>
                    <p class="mb-2 px-3 text-[10px] font-black uppercase tracking-[0.22em] text-indigo-300/70">Académico</p>
                    <a href="{{ route('gestion-academica.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('gestion-academica.*') ? 'bg-white/10 text-white shadow-inner shadow-white/5 ring-1 ring-white/10' : 'text-indigo-100/70 hover:bg-white/5 hover:text-white' }}">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-indigo-300/50 ring-1 ring-white/5">
                            <x-icon name="academic-cap" class="h-4 w-4" />
                        </span>
                        <span>Gestión académica</span>
                    </a>
                </div>

                <div>
                    <p class="mb-2 px-3 text-[10px] font-black uppercase tracking-[0.22em] text-indigo-300/70">Seguridad</p>
                    @if (\Illuminate\Support\Facades\Route::has('admin.bitacora.index'))
                        <a href="{{ route('admin.bitacora.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('admin.bitacora.*') ? 'bg-white/10 text-white shadow-inner shadow-white/5 ring-1 ring-white/10' : 'text-indigo-100/70 hover:bg-white/5 hover:text-white' }}">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="shield" class="h-4 w-4" />
                            </span>
                            <span>Bitácora del sistema</span>
                        </a>
                    @endif
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
                        @if (\Illuminate\Support\Facades\Route::has('monitoreo.index'))
                            <a href="{{ route('monitoreo.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="monitor" class="h-4 w-4" />
                            </span>
                            <span>Monitoreo</span>
                            </a>
                        @endif
                        @if (\Illuminate\Support\Facades\Route::has('diagnosticos.index'))
                            <a href="{{ route('diagnosticos.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-indigo-100/70 transition hover:bg-white/5 hover:text-white">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-cyan-300 ring-1 ring-white/10 transition group-hover:bg-cyan-400/10 group-hover:text-cyan-200">
                                <x-icon name="diagnostic" class="h-4 w-4" />
                            </span>
                            <span>Diagnósticos</span>
                            </a>
                        @endif
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
                                <span x-cloak x-show="unreadNotifications > 0" x-text="unreadNotifications > 9 ? '9+' : unreadNotifications" class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-black text-white ring-2 ring-white"></span>
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
                                    <template x-for="notificacion in notifications" :key="notificacion.id">
                                        <a :href="notificacion.url" class="group flex gap-3 border-b border-slate-50 px-4 py-3 transition hover:bg-indigo-50/70">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100 transition group-hover:bg-white">
                                                <x-icon name="bell" class="h-4 w-4" />
                                            </span>
                                            <span class="min-w-0">
                                                <p class="truncate text-xs font-bold text-slate-800" x-text="notificacion.titulo"></p>
                                                <p class="mt-1 text-xs leading-5 text-slate-500" x-text="notificacion.mensaje"></p>
                                                <p class="mt-1 text-[10px] font-semibold text-slate-400" x-text="notificacion.creada"></p>
                                            </span>
                                        </a>
                                    </template>
                                    <div x-show="notifications.length === 0" class="px-4 py-8 text-center">
                                        <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                                            <x-icon name="bell" class="h-5 w-5" />
                                        </span>
                                        <p class="mt-2 text-xs font-semibold text-slate-500">No hay notificaciones nuevas</p>
                                    </div>
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
                                @if (\Illuminate\Support\Facades\Route::has('security.questions.edit'))
                                    <a href="{{ route('security.questions.edit') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-indigo-50 hover:text-indigo-700">
                                        <x-icon name="key" class="h-4 w-4" />
                                        Preguntas de seguridad
                                    </a>
                                @endif
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

            {{ $slot }}
        </div>
    </div>

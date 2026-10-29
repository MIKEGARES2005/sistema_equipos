<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Portal del estudiante · {{ config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans antialiased">
        @php
            $estado = $estudiante?->estado ?? 'sin perfil';
            $estadoClases = match ($estado) {
                'activo' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'congelado' => 'bg-sky-50 text-sky-700 ring-sky-200',
                default => 'bg-slate-100 text-slate-600 ring-slate-200',
            };
            $iniciales = collect(explode(' ', $user->nombreCompleto()))
                ->filter()
                ->map(fn ($parte) => mb_substr($parte, 0, 1))
                ->take(2)
                ->join('');
            $notificacionesIniciales = collect($notificaciones ?? [])->map(fn ($notificacion): array => [
                'id' => $notificacion->id,
                'titulo' => $notificacion->titulo,
                'resumen' => $notificacion->tipo === 'solicitud_registro'
                    ? (str_contains(mb_strtolower($notificacion->titulo), 'acept') ? 'Solicitud aceptada' : 'Solicitud de registro')
                    : (str_contains(mb_strtolower($notificacion->tipo.' '.$notificacion->titulo), 'asign') ? 'Asignación aceptada' : 'Aviso del sistema'),
                'mensaje' => $notificacion->mensaje,
                'url' => in_array($notificacion->tipo, ['sistema', 'solicitud_aprobada'], true) || $notificacion->url === null
                    ? route('estudiante.notifications.details')
                    : $notificacion->url,
                'creada' => $notificacion->created_at?->diffForHumans(),
            ])->values();
        @endphp

        @if (session('student_welcome'))
            <div x-data="{ abierto: true }" x-show="abierto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="bienvenida-estudiante">
                <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-emerald-900/10">
                    <div class="bg-gradient-to-r from-emerald-700 to-teal-500 px-6 py-7 text-white sm:px-8">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-100">Acceso autorizado</p>
                        <h2 id="bienvenida-estudiante" class="mt-2 text-2xl font-extrabold">¡Bienvenido al portal estudiantil!</h2>
                    </div>
                    <div class="space-y-4 px-6 py-6 text-sm leading-6 text-slate-600 sm:px-8">
                        <p>Tu solicitud fue aprobada y tu perfil ya está habilitado. Desde <strong>Mi perfil</strong> podrás configurar tus datos personales, nombre de usuario y contraseña.</p>
                        <p>También podrás registrar y actualizar tus preguntas de seguridad para recuperar tu cuenta cuando lo necesites.</p>
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-amber-900">
                            <strong>Importante:</strong> cambia inmediatamente tus credenciales temporales y completa tus preguntas de seguridad.
                        </div>
                        <button type="button" @click="abierto = false" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 font-bold text-white shadow-sm transition hover:bg-emerald-800">Cerrar</button>
                    </div>
                </div>
            </div>
        @endif

        <header
            x-data="{
                profileOpen: false,
                notificationsOpen: false,
                notifications: @js($notificacionesIniciales),
                unreadNotifications: {{ $notificacionesNoLeidas ?? 0 }},
                refreshNotifications() {
                    fetch('{{ route('estudiante.notifications.index') }}', { headers: { Accept: 'application/json' } })
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
                    fetch('{{ route('estudiante.notifications.clear') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            Accept: 'application/json'
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
            class="border-b border-emerald-100 bg-white"
        >
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <div class="flex w-full items-center justify-center gap-3 text-center sm:w-auto sm:justify-start sm:text-left">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600 text-lg font-bold text-white shadow-sm">IT</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-700">Portal del estudiante</p>
                        <p class="text-sm font-bold text-slate-800">{{ config('app.name') }}</p>
                    </div>
                    <div class="relative shrink-0 sm:ml-4" @click.outside="notificationsOpen = false">
                        <button type="button" @click="notificationsOpen = !notificationsOpen" :aria-expanded="notificationsOpen" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-100 bg-white text-emerald-600 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50" aria-label="Notificaciones">
                            <x-icon name="bell" class="h-5 w-5" />
                            <span x-cloak x-show="unreadNotifications > 0" x-text="unreadNotifications > 9 ? '9+' : unreadNotifications" class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-black text-white ring-2 ring-white"></span>
                        </button>

                        <div x-cloak x-show="notificationsOpen" x-transition class="fixed inset-x-3 top-20 z-50 max-h-[calc(100dvh-6rem)] overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-2xl shadow-emerald-950/20 sm:absolute sm:inset-x-auto sm:right-0 sm:top-12 sm:max-h-[calc(100vh-7rem)] sm:w-80">
                            <div class="border-b border-emerald-100 bg-gradient-to-r from-emerald-50 to-teal-50 px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-black text-slate-800">Notificaciones</p>
                                        <p class="mt-0.5 text-[11px] text-slate-500">Avisos de tu cuenta y tus solicitudes</p>
                                    </div>
                                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm ring-1 ring-emerald-100">
                                        <x-icon name="bell" class="h-4 w-4" />
                                    </span>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <button type="button" @click="clearNotifications()" class="inline-flex min-w-0 flex-1 items-center justify-center rounded-lg bg-white px-2 py-2 text-[11px] font-bold text-rose-600 shadow-sm ring-1 ring-rose-100 transition hover:bg-rose-600 hover:text-white">
                                    Limpiar bandeja
                                    </button>
                                    <a href="{{ route('estudiante.notifications.details') }}" class="inline-flex min-w-0 flex-1 items-center justify-center rounded-lg bg-emerald-700 px-2 py-2 text-[11px] font-bold text-white shadow-sm transition hover:bg-emerald-800">
                                        Más detalles
                                    </a>
                                </div>
                            </div>
                            <div class="max-h-[calc(100dvh-14rem)] overflow-y-auto overscroll-contain sm:max-h-80">
                                <template x-for="notificacion in notifications" :key="notificacion.id">
                                    <a :href="notificacion.url" class="group flex gap-3 border-b border-slate-50 px-4 py-3 transition hover:bg-emerald-50/70">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                                            <x-icon name="bell" class="h-4 w-4" />
                                        </span>
                                        <span class="min-w-0">
                                            <p class="truncate text-xs font-bold text-slate-800" x-text="notificacion.resumen || notificacion.titulo"></p>
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
                </div>
                <div class="flex w-full flex-nowrap items-center justify-center gap-2 sm:w-auto sm:justify-end sm:gap-3">
                    <div class="relative min-w-0" @click.outside="profileOpen = false">
                        <button type="button" @click="profileOpen = !profileOpen" :aria-expanded="profileOpen" aria-haspopup="true" class="flex min-w-0 max-w-[13.5rem] items-center gap-2 rounded-2xl border border-emerald-100 bg-white px-2 py-2 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50/60 sm:max-w-none sm:gap-3 sm:px-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-teal-500 text-sm font-black text-white shadow-sm">{{ $iniciales ?: 'E' }}</span>
                            <span class="min-w-0 text-left">
                                <span class="block max-w-[8.5rem] truncate text-sm font-bold text-slate-800 sm:max-w-[12rem]">{{ $user->nombreCompleto() }}</span>
                                <span class="mt-0.5 block max-w-[8.5rem] truncate text-xs font-medium text-slate-500 sm:max-w-[12rem]">{{ '@'.$user->username }}</span>
                            </span>
                            <svg class="h-4 w-4 text-emerald-600 transition-transform" :class="{ 'rotate-180': profileOpen }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-cloak x-show="profileOpen" x-transition class="absolute left-0 right-0 top-full z-40 mt-2 w-full overflow-hidden rounded-2xl border border-emerald-100 bg-white p-2 shadow-2xl shadow-emerald-950/15 sm:left-auto sm:right-0 sm:w-64">
                            <div class="border-b border-slate-100 px-3 py-2">
                                <p class="truncate text-xs font-bold text-slate-800">{{ $user->nombreCompleto() }}</p>
                                <p class="mt-0.5 truncate text-[11px] text-slate-500">{{ '@'.$user->username }}</p>
                            </div>
                            <a href="{{ route('estudiante.profile.edit') }}" class="mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700" aria-hidden="true">◎</span>
                                Mi perfil
                            </a>
                            <a href="{{ route('estudiante.security.questions.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-amber-50 hover:text-amber-700">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700" aria-hidden="true">✓</span>
                                Preguntas de seguridad
                            </a>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('estudiante.logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-600 bg-rose-600 px-3 py-2 text-sm font-bold text-white shadow-sm transition hover:border-rose-700 hover:bg-rose-700 sm:px-4">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-500 p-6 text-white shadow-lg sm:p-8">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-100">Bienvenido</p>
                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold ring-1 ring-white/25">
                                {{ strtoupper($estado) }}
                            </span>
                        </div>
                        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $user->nombreCompleto() }}</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50">
                            Desde aquí podrás solicitar equipos, revisar tus préstamos y mantener actualizados tus datos de contacto.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-sm">
                        <span class="rounded-full bg-white/15 px-3 py-1 font-semibold ring-1 ring-white/25">
                            Matrícula: {{ $estudiante?->matricula ?? 'No registrada' }}
                        </span>
                        <span class="rounded-full bg-white/15 px-3 py-1 font-semibold ring-1 ring-white/25">
                            Semestre: {{ $estudiante?->semestre ?? '—' }}
                        </span>
                    </div>
                </div>
            </section>

            @if ($estudiante === null)
                <div role="alert" class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                    Tu cuenta todavía no tiene un perfil de estudiante asociado. Contacta a administración para completar tu registro.
                </div>
            @endif

            @if (false && ($notificaciones ?? collect())->isNotEmpty())
                <section class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">🔔</span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Avisos importantes</h2>
                            <p class="text-sm text-slate-500">Mensajes relacionados con tu cuenta y tus solicitudes.</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach ($notificaciones as $notificacion)
                            <a href="{{ $notificacion->url ?: route('estudiante.dashboard') }}" class="block rounded-xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 transition hover:bg-emerald-50">
                                <p class="text-sm font-bold text-emerald-900">{{ $notificacion->titulo }}</p>
                                <p class="mt-1 text-sm leading-6 text-emerald-800">{{ $notificacion->mensaje }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('estudiante.profile.edit') }}" class="hidden">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-lg text-emerald-700" aria-hidden="true">♙</span>
                    <h2 class="mt-4 font-bold text-slate-800">Mi perfil</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Actualiza tus datos de contacto y tu contraseña.</p>
                    <span class="mt-3 inline-block text-xs font-semibold text-emerald-700 group-hover:underline">Abrir</span>
                </a>

                <a href="{{ route('estudiante.security.questions.edit') }}" class="hidden">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-lg text-amber-700" aria-hidden="true">♢</span>
                    <h2 class="mt-4 font-bold text-slate-800">Preguntas de seguridad</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Configúralas para recuperar tu contraseña sin ayuda.</p>
                    <span class="mt-3 inline-block text-xs font-semibold text-emerald-700 group-hover:underline">Configurar</span>
                </a>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-lg text-slate-500" aria-hidden="true">▣</span>
                    <h2 class="mt-4 font-bold text-slate-700">Solicitar equipo</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Pide un equipo para tus prácticas de laboratorio.</p>
                    <span class="mt-3 inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Próximamente</span>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-lg text-slate-500" aria-hidden="true">▤</span>
                    <h2 class="mt-4 font-bold text-slate-700">Mis préstamos</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Consulta tus equipos asignados y sus devoluciones.</p>
                    <span class="mt-3 inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Próximamente</span>
                </div>
            </div>

            <section class="grid gap-6">
                <aside class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm sm:p-7">
                    <h2 class="text-lg font-bold text-emerald-900">¿Necesitas ayuda?</h2>
                    <p class="mt-2 text-sm leading-6 text-emerald-800">
                        Si no puedes acceder o tus datos no son correctos, acude al laboratorio de sistemas o contacta al administrador del plantel.
                    </p>
                    <div class="mt-5 flex flex-col items-center gap-3 border-t border-emerald-200 pt-5 text-center">
                        <p class="text-sm font-semibold text-emerald-900">
                            Ing. Magaly Escobar Guarachi
                        </p>
                        <a
                            href="https://wa.me/59165614904?text=Hola%20Ing.%20Magaly,%20necesito%20ayuda%20con%20el%20Portal%20del%20Estudiante."
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-center text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 sm:w-auto"
                        >
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2a9.9 9.9 0 0 0-8.55 14.9L2 22l5.25-1.38A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.08-1.12l-.3-.18-3.12.82.83-3.04-.2-.31A8 8 0 1 1 12 20Zm4.38-5.98c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.94-1.2-.72-.64-1.2-1.42-1.34-1.66-.14-.24-.02-.37.1-.49.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.47-.4-.4-.54-.41h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.68 2.56 4.06 3.58.57.25 1.02.4 1.37.51.58.18 1.11.15 1.53.09.47-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z" />
                            </svg>
                            Contactar con administración
                        </a>
                    </div>
                </aside>
            </section>
        </main>

        <x-flash-toasts />
    </body>
</html>

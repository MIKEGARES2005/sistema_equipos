<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Notificaciones · Portal del estudiante</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans antialiased">
        <header class="border-b border-emerald-100 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-lg font-bold text-white shadow-sm">IT</span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-700">Portal del estudiante</p>
                        <p class="truncate text-sm font-bold text-slate-800">{{ config('app.name') }}</p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('estudiante.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50 sm:px-4 sm:text-sm">
                        Volver al panel
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <section class="rounded-3xl bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-500 p-6 text-white shadow-lg sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-100">Portal del estudiante / Notificaciones</p>
                <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-black sm:text-3xl">Notificaciones</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-emerald-50">Consulta con detalle los avisos relacionados con tu cuenta, solicitudes y actividades académicas.</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-white/15 px-3 py-2 text-xs font-bold ring-1 ring-white/25">
                        {{ $notificacionesNoLeidas }} sin leer
                    </span>
                </div>
            </section>

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="text-lg font-black text-slate-800">Bandeja completa</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $notificaciones->count() }} notificaciones registradas.</p>
                    </div>
                    <form method="POST" action="{{ route('estudiante.notifications.clear') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-700 shadow-sm transition hover:bg-rose-50">
                            Limpiar bandeja
                        </button>
                    </form>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($notificaciones as $notificacion)
                        @php
                            $esAvisoSistema = in_array($notificacion->tipo, ['sistema', 'solicitud_aprobada'], true);
                        @endphp
                        <div class="flex gap-4 px-5 py-5 sm:px-6">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                                <x-icon name="bell" class="h-5 w-5" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                    <span class="text-sm font-black text-slate-800">{{ $esAvisoSistema ? 'Aviso del sistema' : $notificacion->titulo }}</span>
                                    <span class="shrink-0 text-[11px] font-semibold text-slate-400">{{ $notificacion->created_at?->format('d/m/Y H:i') }}</span>
                                </span>
                                <span class="mt-2 block text-sm leading-6 text-slate-600">{{ $notificacion->mensaje }}</span>
                                @if ($esAvisoSistema)
                                    <span class="mt-3 block rounded-xl border border-amber-200 bg-amber-50 px-3 py-3 text-xs font-semibold leading-5 text-amber-900">
                                        Importante: cambia inmediatamente tus credenciales y registra tus preguntas de seguridad desde tu perfil.
                                    </span>
                                @endif
                                <span class="mt-2 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $notificacion->tipo }}</span>
                            </span>
                        </div>
                    @empty
                        <div class="px-6 py-16 text-center">
                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 ring-1 ring-slate-100">
                                <x-icon name="bell" class="h-7 w-7" />
                            </span>
                            <h2 class="mt-4 text-base font-black text-slate-700">No tienes notificaciones</h2>
                            <p class="mt-1 text-sm text-slate-500">Tu bandeja está limpia por ahora.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>
    </body>
</html>

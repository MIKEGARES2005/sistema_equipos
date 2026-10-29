<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistema de Gestión') }}</title>

    <!-- Fuente -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap"
        rel="stylesheet"
    />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-screen bg-slate-950 antialiased selection:bg-cyan-400/30 selection:text-white"
    style="font-family: 'Manrope', sans-serif;"
>
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950">
        {{-- Fondo decorativo --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="login-orb absolute -left-32 -top-32 h-96 w-96 rounded-full bg-cyan-500/15 blur-3xl"></div>
            <div class="login-orb login-orb-delayed absolute -bottom-40 -right-24 h-[30rem] w-[30rem] rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="login-grid absolute inset-0 opacity-50"></div>
        </div>

        <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
            <div class="w-full min-w-0 max-w-[58rem] overflow-hidden rounded-[1.75rem] border border-white/10 bg-white shadow-2xl shadow-slate-950/60">
                <div class="grid grid-cols-1 lg:grid-cols-2">
                    {{-- Identidad del sistema --}}
                    <section class="relative min-w-0 overflow-hidden bg-gradient-to-br from-slate-900 via-blue-950 to-indigo-950 px-6 py-8 text-white sm:px-9 lg:min-h-[500px] lg:px-10 lg:py-10">
                        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                            <div class="login-orb absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/15 blur-3xl"></div>
                            <div class="login-orb login-orb-delayed absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                            <div class="login-grid absolute inset-0 opacity-40"></div>
                        </div>

                        <div class="relative flex h-full flex-col justify-between">
                            <div class="max-w-md">
                                <span class="inline-flex items-start gap-1 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-2 py-1.5 text-[9px] font-bold uppercase leading-4 tracking-normal text-cyan-200 sm:gap-2 sm:px-3.5 sm:text-[10px] sm:tracking-[0.03em]">
                                    <span class="mt-[5px] h-1.5 w-1.5 shrink-0 rounded-full bg-cyan-300 shadow-[0_0_10px_rgba(103,232,249,0.9)]"></span>
                                    INSTITUTO TECNOLOGICO INDUSTRIAL &quot;BRASIL - BOLIVIA&quot;
                                </span>

                                <h1 class="mt-5 text-3xl font-black leading-[1.05] tracking-tight text-white sm:text-4xl">
                                    Informática
                                    <span class="block bg-gradient-to-r from-cyan-300 via-sky-200 to-indigo-300 bg-clip-text text-transparent">
                                        Industrial
                                    </span>
                                </h1>

                                <div class="mt-4 h-1.5 w-20 rounded-full bg-gradient-to-r from-cyan-400 via-sky-400 to-indigo-500 shadow-[0_0_24px_rgba(34,211,238,0.55)]"></div>

                                <h2 class="mt-5 text-lg font-semibold leading-tight text-white/95 sm:text-xl">
                                    Gestión de Equipos y Laboratorio
                                </h2>
                                <p class="mt-2 max-w-sm text-sm leading-6 text-slate-300/90">
                                    Monitoreo, control y diagnóstico en tiempo real.
                                </p>

                                <div class="mt-5 flex flex-wrap gap-1.5">
                                    <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-wide text-slate-300">
                                        Monitoreo en vivo
                                    </span>
                                    <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-wide text-slate-300">
                                        Control de equipos
                                    </span>
                                    <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-wide text-slate-300">
                                        Diagnóstico
                                    </span>
                                </div>
                            </div>

                            @php($brandingActions = trim($__env->yieldPushContent('branding-actions')))
                            @if ($brandingActions !== '')
                                <div class="mt-6">
                                    {!! $brandingActions !!}
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- Formulario --}}
                    <section class="flex min-w-0 items-center bg-white px-6 py-8 sm:px-9 lg:px-10 lg:py-10">
                        <div class="mx-auto w-full max-w-md">
                            {{ $slot }}
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    @stack('modals')

    <x-flash-toasts />
</body>
</html>

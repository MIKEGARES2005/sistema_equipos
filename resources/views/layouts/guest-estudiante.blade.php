@props(['showStudentLink' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistema de Gestión') }} · Portal del Estudiante</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap"
        rel="stylesheet"
    />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased selection:bg-emerald-300/30 selection:text-white" style="font-family: 'Manrope', sans-serif;">
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-[#064E3B] via-[#0F766E] to-[#134E4A]">
        {{-- Decoración de fondo --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="login-orb absolute -left-32 -top-32 h-96 w-96 rounded-full bg-emerald-300/25 blur-3xl"></div>
            <div class="login-orb login-orb-delayed absolute -bottom-40 -right-24 h-[30rem] w-[30rem] rounded-full bg-cyan-300/20 blur-3xl"></div>
            <div class="absolute right-[12%] top-[14%] h-24 w-24 animate-pulse rounded-full border border-emerald-200/20"></div>
            <div class="absolute bottom-[18%] left-[12%] h-16 w-16 rounded-full border border-cyan-200/20"></div>
            <div class="login-grid absolute inset-0 opacity-40"></div>
        </div>

        <main class="relative z-10 flex min-h-screen flex-col items-center justify-center gap-6 p-4 sm:p-6">
            <div class="w-full max-w-md overflow-hidden rounded-[2rem] border border-white/70 bg-white p-6 shadow-2xl shadow-emerald-950/35 ring-1 ring-white/80 sm:p-8">
                {{-- Identidad del portal --}}
                <div class="mb-8 text-center">
                    <div class="relative mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 via-teal-600 to-cyan-700 text-white shadow-xl shadow-emerald-900/25 ring-4 ring-emerald-100">
                        <span class="absolute inset-0 rounded-2xl bg-emerald-300/30 blur-md" aria-hidden="true"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.44 60.44 0 0 0-.491 6.347A48.63 48.63 0 0 1 12 18.9a48.63 48.63 0 0 1 8.232-2.406 60.44 60.44 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.9 59.9 0 0 1 12 3.493a59.9 59.9 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.7 50.7 0 0 1 12 13.489a50.7 50.7 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.4 55.4 0 0 1 12 8.443m-7.007 11.55A5.98 5.98 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.35em] text-emerald-700">
                        ITIBB
                    </p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-800 sm:text-3xl">
                        Portal del Estudiante
                    </h1>
                    <p class="mt-2 text-sm text-slate-500">
                        Acceso exclusivo para estudiantes del Instituto.
                    </p>
                    <div class="mx-auto mt-4 h-1 w-16 rounded-full bg-gradient-to-r from-emerald-400 via-teal-500 to-cyan-500"></div>
                </div>

                {{ $slot }}

                @if ($showStudentLink)
                    <div class="mt-8 border-t border-slate-200 pt-5 text-center">
                        <a
                            href="{{ route('estudiante.login') }}"
                            class="block text-center text-sm font-bold text-emerald-700 transition hover:text-teal-800 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                        >
                            Volver al Portal del Estudiante
                        </a>
                    @if (false)
                        {{-- <a
                            href="{{ route('login') }}"
                            class="text-xs font-semibold text-slate-500 transition hover:text-emerald-700 hover:underline"
                        >
                            ¿Eres administrador o docente? Ingresa aquí
                        </a> --}}
                    @endif
                    </div>
                @endif
            </div>

            <p class="text-center text-xs text-white/70">
                Gestión de equipos de cómputo · Informática Industrial
            </p>
        </main>
    </div>

    <x-flash-toasts />
</body>
</html>

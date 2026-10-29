@php
    $esEstudiante = $user->tipo_usuario === 'estudiante';
    $esAdministradorPuro = $user->tipo_usuario === 'administrador' && $user->docente === null;
    $perfilPersonal = $user->docente ?? $user->estudiante;
    $nombrePerfil = $user->nombreCompleto();
    $fotoPerfil = $perfilPersonal?->foto_personal;
    $fotoPerfilDisponible = $fotoPerfil
        && \Illuminate\Support\Facades\Storage::disk('public')->exists($fotoPerfil);
    $fotoPerfilUrl = $fotoPerfilDisponible
        ? ($esEstudiante ? route('estudiante.profile.photo') : route('profile.photo'))
        : null;
    $inicialesPerfil = collect(explode(' ', $nombrePerfil ?: $user->username))
        ->filter()
        ->map(fn ($parte) => mb_substr($parte, 0, 1))
        ->take(2)
        ->join('');
    $cargoPerfil = $user->docente?->cargo
        ?? ($esEstudiante ? 'Estudiante · '.$user->estudiante?->matricula : null);
    $contextoPerfil = $esEstudiante ? 'Portal del estudiante' : 'Administración';
    $tituloPerfil = $esAdministradorPuro ? 'Perfil del administrador' : 'Mi perfil';
@endphp

<main class="mx-auto max-w-7xl space-y-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] {{ $esEstudiante ? 'text-emerald-600' : 'text-indigo-500' }}">
                <span>{{ $contextoPerfil }}</span>
                <span class="text-slate-300">/</span>
                <span>Mi cuenta</span>
                <span class="text-slate-300">/</span>
                <span class="text-slate-400">Perfil</span>
            </div>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">{{ $tituloPerfil }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                Actualiza tus datos personales, la información de tu cuenta y la contraseña.
            </p>
        </div>
        @if (! $esAdministrador)
                <div class="flex flex-col gap-2 sm:flex-row">
                    @if (! $esEstudiante && ! $esAdministrador)
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-bold text-rose-700 shadow-sm transition hover:bg-rose-50 sm:w-auto">
                        <x-icon name="logout" class="h-4 w-4" />
                        Cerrar sesión
                    </button>
                </form>
            </div>
        @endif
        @endif
    </header>

    @if ($user->debe_cambiar_password)
        <div role="alert" class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-100 font-bold" aria-hidden="true">!</span>
            <span>Tu contraseña es temporal. Actualízala en el panel de seguridad antes de continuar usando el sistema.</span>
        </div>
    @endif

    <section class="dashboard-rise relative -mt-3 overflow-hidden rounded-3xl px-6 py-7 text-white shadow-2xl sm:-mt-2 sm:px-8 sm:py-8 {{ $esEstudiante ? 'bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-500 shadow-emerald-950/20' : 'bg-gradient-to-br from-[#0B1226] via-[#1E2A5A] to-[#272F7A] shadow-indigo-950/20' }}">
        @if ($esEstudiante)
            <div class="absolute right-4 top-4 z-10 hidden sm:right-8 sm:top-8 sm:block">
                <a href="{{ route('estudiante.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-white/70 bg-white px-3 py-2 text-xs font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50 sm:px-4 sm:py-2.5 sm:text-sm">
                    Volver al panel
                </a>
            </div>
        @endif
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 left-1/3 h-64 w-64 rounded-full bg-indigo-400/20 blur-3xl" aria-hidden="true"></div>
        <div class="dashboard-grid pointer-events-none absolute inset-0 opacity-30" aria-hidden="true"></div>

        <div class="relative hidden flex-row items-center gap-6 sm:flex sm:pr-48">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-3xl border border-white/25 bg-white/10 text-2xl font-black shadow-inner sm:h-24 sm:w-24 sm:text-3xl">
                @if ($fotoPerfilDisponible)
                    <img src="{{ $fotoPerfilUrl }}" alt="Fotografía de {{ $nombrePerfil }}" class="h-full w-full object-cover">
                @else
                    {{ $inicialesPerfil ?: 'U' }}
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-2.5 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] text-cyan-100 sm:gap-2 sm:px-3 sm:text-[10px] sm:tracking-[0.18em]">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300 shadow-[0_0_10px_rgba(110,231,183,0.9)]"></span>
                        Perfil de usuario
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-2.5 py-1.5 text-[9px] font-bold uppercase tracking-[0.1em] text-indigo-100 sm:gap-2 sm:px-3 sm:text-[10px] sm:tracking-[0.14em]">
                        <x-icon name="shield" class="h-3.5 w-3.5 text-cyan-200" />
                        Cuenta protegida
                    </span>
                </div>

                <h2 class="mt-2 break-words text-xl font-black leading-tight tracking-tight sm:mt-4 sm:text-3xl">{{ $nombrePerfil }}</h2>

                <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs sm:mt-3 sm:gap-2 sm:text-sm">
                    <span class="max-w-full truncate rounded-full bg-white/10 px-2.5 py-1 font-semibold text-indigo-100 ring-1 ring-white/15 sm:px-3">{{ '@'.$user->username }}</span>
                    <span class="rounded-full bg-emerald-400/20 px-2.5 py-1 font-bold text-emerald-100 ring-1 ring-emerald-200/30 sm:px-3">{{ ucfirst($user->tipo_usuario) }}</span>
                    @if ($cargoPerfil && ! $esEstudiante)
                        <span class="text-indigo-100/80">{{ $cargoPerfil }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="relative z-10 space-y-4 sm:hidden">
            <div class="flex items-start justify-between gap-4">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-3xl border border-white/25 bg-white/10 text-3xl font-black shadow-inner">
                    @if ($fotoPerfilDisponible)
                        <img src="{{ $fotoPerfilUrl }}" alt="FotografÃ­a de {{ $nombrePerfil }}" class="h-full w-full object-cover">
                    @else
                        {{ $inicialesPerfil ?: 'U' }}
                    @endif
                </div>
                <a href="{{ route('estudiante.dashboard') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl border border-white/80 bg-white px-3 py-2.5 text-xs font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                    Volver al panel
                </a>
            </div>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-2.5 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] text-cyan-100">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Perfil de usuario
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-2.5 py-1.5 text-[9px] font-bold uppercase tracking-[0.1em] text-indigo-100">
                        <x-icon name="shield" class="h-3.5 w-3.5 text-cyan-200" />
                        Cuenta protegida
                    </span>
                </div>
                <h2 class="mt-3 break-words text-2xl font-black leading-tight tracking-tight">{{ $nombrePerfil }}</h2>
                <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="max-w-full truncate rounded-full bg-white/10 px-2.5 py-1 font-semibold text-indigo-100 ring-1 ring-white/15">{{ '@'.$user->username }}</span>
                    <span class="rounded-full bg-emerald-400/20 px-2.5 py-1 font-bold text-emerald-100 ring-1 ring-emerald-200/30">{{ ucfirst($user->tipo_usuario) }}</span>
                </div>
            </div>
        </div>
    </section>

    <div class="space-y-6">
        <section class="dashboard-rise overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
            <div class="p-5 sm:p-7">
                @include('profile.partials.update-profile-information-form')
            </div>
        </section>

    </div>
</main>

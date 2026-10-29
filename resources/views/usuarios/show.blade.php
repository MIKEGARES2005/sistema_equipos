@php
    $perfil = match ($usuario->tipo_usuario) {
        'estudiante' => $usuario->estudiante,
        default => $usuario->docente,
    };

    $nombreCompleto = trim(collect([
        $perfil?->nombres,
        $perfil?->apellido_paterno,
        $perfil?->apellido_materno,
    ])->filter()->implode(' '));

    $nombreMostrado = $nombreCompleto ?: ($usuario->name ?: $usuario->username);
    $fotoPersonal = $perfil?->foto_personal;
    $fotoDisponible = $fotoPersonal
        && \Illuminate\Support\Facades\Storage::disk('public')->exists($fotoPersonal);
    $perfilActivo = $perfil
        ? strtolower((string) $perfil->estado) === 'activo'
        : (bool) $usuario->activo;
    $estadoTexto = $perfilActivo ? 'Activo' : 'Inactivo';
    $esCuentaPropia = $usuario->id === auth()->id();
    $iniciales = collect(explode(' ', $nombreMostrado))
        ->filter()
        ->map(fn (string $parte): string => mb_substr($parte, 0, 1))
        ->take(2)
        ->join('');

    $datosPerfil = $usuario->tipo_usuario === 'estudiante'
        ? [
            ['label' => 'Nombres', 'value' => $perfil?->nombres],
            ['label' => 'Apellido paterno', 'value' => $perfil?->apellido_paterno],
            ['label' => 'Apellido materno', 'value' => $perfil?->apellido_materno],
            ['label' => 'Carnet de identidad', 'value' => trim(($perfil?->ci ?? '').' '.($perfil?->ci_extension ?? ''))],
            ['label' => 'Teléfono', 'value' => $perfil?->telefono],
            ['label' => 'Correo personal', 'value' => $perfil?->correo_personal],
            ['label' => 'Matrícula', 'value' => $perfil?->matricula],
            ['label' => 'Semestre', 'value' => $perfil?->semestre],
            ['label' => 'Generación de ingreso', 'value' => $perfil?->generacion_ingreso],
            ['label' => 'Correo institucional', 'value' => $perfil?->correo_institucional],
            ['label' => 'Dirección', 'value' => $perfil?->direccion, 'wide' => true],
        ]
        : [
            ['label' => 'Nombres', 'value' => $perfil?->nombres],
            ['label' => 'Apellido paterno', 'value' => $perfil?->apellido_paterno],
            ['label' => 'Apellido materno', 'value' => $perfil?->apellido_materno],
            ['label' => 'Carnet de identidad', 'value' => trim(($perfil?->ci ?? '').' '.($perfil?->ci_extension ?? ''))],
            ['label' => 'Teléfono', 'value' => $perfil?->telefono],
            ['label' => 'Correo personal', 'value' => $perfil?->correo_personal],
            ['label' => 'Cargo', 'value' => $perfil?->cargo],
            ['label' => 'Especialidad', 'value' => $perfil?->especialidad],
            ['label' => 'Nivel académico', 'value' => $perfil?->nivel_academico],
            ['label' => 'Fecha de ingreso', 'value' => $perfil?->fecha_ingreso?->format('d/m/Y')],
        ];
@endphp

<x-admin-layout>
    <x-admin-shell>
        <div
            class="min-h-screen bg-[#F4F7FC] text-slate-800"
            x-data="{
                showRecover: false,
                loading: false,
                success: false,
                error: '',
                newUsername: '',
                newPassword: '',
                recoveryUrl: @js(route('usuarios.recover', ['id' => $usuario->id])),
                closeRecover() {
                    this.showRecover = false;
                    this.loading = false;
                    this.success = false;
                    this.error = '';
                    this.newUsername = '';
                    this.newPassword = '';
                    this.$refs.recoverForm?.reset();
                },
                async recoverAccount() {
                    const adminPassword = this.$refs.recoverForm.admin_password.value;
                    this.error = '';
                    this.success = false;

                    if (!adminPassword) {
                        this.error = 'Introduce la contraseña de administrador.';
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch(this.recoveryUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ admin_password: adminPassword })
                        });
                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            this.error = data.message || 'No se pudo recuperar la cuenta.';
                            return;
                        }

                        this.newUsername = data.username;
                        this.newPassword = data.password;
                        this.success = true;
                        this.$refs.recoverForm.admin_password.value = '';
                    } catch {
                        this.error = 'No se pudo conectar con el servidor. Inténtalo de nuevo.';
                    } finally {
                        this.loading = false;
                    }
                }
            }"
            @keydown.escape.window="closeRecover()"
        >
            <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0B1226] via-[#1E2A5A] to-[#272F7A] px-6 py-7 text-white shadow-2xl shadow-indigo-950/20 sm:px-8 sm:py-8">
                    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"></div>
                    <div class="pointer-events-none absolute inset-0 dashboard-grid opacity-30"></div>
                    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-cyan-200">Administración / Usuarios / Detalles</p>
                            <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">Detalles del usuario</h1>
                            <p class="mt-2 text-sm leading-6 text-indigo-100/80">Consulta la información de la cuenta y sus datos personales.</p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('usuarios.index', ['categoria' => $usuario->tipo_usuario]) }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-bold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Volver al listado</a>
                            <a href="{{ route('usuarios.export', $usuario->id) }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-bold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Exportar plantilla</a>
                        </div>
                    </div>
                </header>

                <section class="rounded-3xl bg-gradient-to-br from-[#0B1226] via-[#1E2A5A] to-[#272F7A] p-6 text-white shadow-2xl shadow-indigo-950/20 sm:p-8">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                        <div class="flex h-36 w-36 shrink-0 items-center justify-center self-center overflow-hidden rounded-3xl border border-white/25 bg-white/10 text-4xl font-black text-cyan-100 shadow-xl ring-4 ring-white/10 sm:self-auto">
                            @if ($fotoDisponible)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($fotoPersonal) }}" alt="Fotografía de {{ $nombreMostrado }}" class="h-full w-full object-cover">
                            @else
                                {{ $iniciales ?: 'U' }}
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><span class="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em] text-cyan-100">Perfil de usuario</span><span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-indigo-100">{{ ucfirst($usuario->tipo_usuario) }}</span></div>
                            <h2 class="mt-4 break-words text-2xl font-black tracking-tight sm:text-4xl">{{ $nombreMostrado }}</h2>
                            <p class="mt-2 break-words text-sm text-indigo-100/80">{{ '@'.$usuario->username }}</p>
                            <span class="mt-5 inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-black {{ $perfilActivo ? 'bg-emerald-400/20 text-emerald-100 ring-1 ring-emerald-200/30' : 'bg-rose-400/20 text-rose-100 ring-1 ring-rose-200/30' }}"><span class="h-2 w-2 rounded-full {{ $perfilActivo ? 'bg-emerald-300' : 'bg-rose-300' }}"></span>{{ $estadoTexto }}</span>
                        </div>
                    </div>
                </section>

                @if (!($usuario->tipo_usuario === 'administrador' && $esCuentaPropia))
                <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5"><h2 class="text-lg font-black text-slate-800">Acciones de la cuenta</h2><p class="mt-1 text-sm text-slate-500">Administra los datos y el acceso de este usuario.</p></div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @if ($perfil)
                            <a href="{{ route('usuarios.personal.edit', $usuario->id) }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#272F7A] px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-[#4959A6]">Editar datos personales</a>
                        @endif
                        @if ($usuario->id !== auth()->id())
                            <button type="button" @click="showRecover = true; success = false; error = ''; newUsername = ''; newPassword = ''" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-emerald-700">Recuperar cuenta</button>
                        @endif
                        @if ($perfil)
                            <form method="POST" action="{{ route('usuarios.status', $usuario->id) }}" onsubmit="return confirm('{{ $perfilActivo ? '¿Deseas desactivar esta cuenta?' : '¿Deseas activar esta cuenta?' }}');">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="estado" value="{{ $perfilActivo ? 'inactivo' : 'activo' }}">
                                <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl px-4 py-3 text-center text-sm font-semibold text-white transition {{ $perfilActivo ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">{{ $perfilActivo ? 'Desactivar cuenta' : 'Activar cuenta' }}</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('usuarios.destroy', $usuario->id) }}" onsubmit="return confirm('Esta accion archivara el perfil y su cuenta. Deseas continuar?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl border-2 border-rose-600 bg-white px-4 py-3 text-center text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Eliminar perfil</button>
                        </form>
                    </div>
                </section>
                @endif

                <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5"><h2 class="text-lg font-black text-slate-800">Información de la cuenta</h2><p class="mt-1 text-sm text-slate-500">Datos asociados al acceso al sistema.</p></div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Nombre de usuario</p><p class="mt-2 break-words font-semibold text-slate-800">{{ $usuario->username ?: 'No registrado' }}</p></div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Tipo de usuario</p><p class="mt-2 font-semibold text-slate-800">{{ ucfirst($usuario->tipo_usuario) }}</p></div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Última actualización</p><p class="mt-2 font-semibold text-slate-800">{{ $usuario->updated_at?->format('d/m/Y H:i') ?? 'No disponible' }}</p></div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex items-center gap-3"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-xl" aria-hidden="true">{{ $usuario->tipo_usuario === 'estudiante' ? '🎓' : '📚' }}</span><div><h2 class="text-lg font-black text-slate-800">{{ $usuario->tipo_usuario === 'estudiante' ? 'Información del estudiante' : 'Información del docente' }}</h2><p class="mt-1 text-sm text-slate-500">Datos personales y {{ $usuario->tipo_usuario === 'estudiante' ? 'académicos' : 'laborales' }} registrados.</p></div></div>
                    @if ($perfil)
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach ($datosPerfil as $dato)
                                <div class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 p-4 {{ ($dato['wide'] ?? false) ? 'sm:col-span-2' : '' }}"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $dato['label'] }}</p><p class="mt-2 break-words text-sm font-semibold text-slate-800">{{ filled($dato['value']) ? $dato['value'] : 'No registrado' }}</p></div>
                            @endforeach
                        </div>
                    @else
                        <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">Esta cuenta todavía no tiene un registro personal asociado.</p>
                    @endif
                </section>

                <div class="flex w-full items-start gap-3 rounded-2xl border border-indigo-100 bg-indigo-50/70 px-5 py-4 shadow-sm"><x-icon name="shield" class="mt-1 h-5 w-5 shrink-0 text-indigo-700" /><p class="text-sm leading-6 text-slate-600"><span class="font-bold text-slate-700">Seguridad:</span> las contraseñas y las respuestas a las preguntas de seguridad no se muestran por este medio.</p></div>
            </main>

            <div x-cloak x-show="showRecover && !success" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="closeRecover()" role="presentation">
                <section x-show="showRecover" x-transition @click.stop class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl sm:p-8" role="dialog" aria-modal="true" aria-labelledby="recover-account-title">
                    <div class="mb-5 flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-600">Seguridad</p><h2 id="recover-account-title" class="mt-1 text-lg font-bold text-slate-800">Recuperar cuenta</h2></div><button type="button" @click="closeRecover()" class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-3xl font-black leading-none text-slate-600 transition hover:bg-rose-100 hover:text-rose-700" aria-label="Cerrar">&times;</button></div>
                    <form x-ref="recoverForm" @submit.prevent="recoverAccount()" class="space-y-4">
                        <p class="text-sm leading-6 text-slate-600">Confirma tu identidad con tu contraseña de administrador. Las credenciales se mostrarán una sola vez.</p>
                        <div><label for="admin_password" class="mb-1.5 block text-sm font-semibold text-slate-700">Contraseña de administrador</label><input id="admin_password" type="password" name="admin_password" autocomplete="current-password" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]" placeholder="Escribe tu contraseña"></div>
                        <div x-cloak x-show="success" x-transition class="rounded-xl border border-emerald-200 bg-emerald-50 p-4" role="status"><p class="font-semibold text-emerald-800">Credenciales temporales generadas</p><p class="mt-2 break-words text-sm text-emerald-900"><span class="font-bold">Usuario:</span> <span x-text="newUsername"></span></p><p class="mt-1 break-words text-sm text-emerald-900"><span class="font-bold">Contraseña:</span> <span x-text="newPassword"></span></p><p class="mt-3 text-xs text-emerald-700">Comunícale estos datos al titular. Deberá cambiar la contraseña al iniciar sesión.</p></div>
                        <p x-cloak x-show="error" x-text="error" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800" role="alert"></p>
                        <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end"><button type="button" @click="closeRecover()" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Cerrar</button><button type="submit" :disabled="loading || success" class="inline-flex items-center justify-center rounded-xl bg-[#C0392B] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-wait disabled:opacity-60"><span x-show="!loading">Generar credenciales</span><span x-cloak x-show="loading">Procesando...</span></button></div>
                    </form>
                </section>
            </div>
            <div x-cloak x-show="showRecover && success" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" @click.self="closeRecover()" role="presentation">
                <section x-show="showRecover && success" x-transition @click.stop class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl sm:p-8" role="dialog" aria-modal="true" aria-labelledby="temporary-credentials-title">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-600">Proceso completado</p>
                            <h2 id="temporary-credentials-title" class="mt-1 text-xl font-black text-slate-800">Credenciales temporales</h2>
                        </div>
                        <button type="button" @click="closeRecover()" class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-3xl font-black leading-none text-slate-600 transition hover:bg-rose-100 hover:text-rose-700" aria-label="Cerrar">&times;</button>
                    </div>
                    <div class="space-y-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                        <div class="rounded-xl border border-emerald-200 bg-white p-4"><p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Nombre de usuario</p><p class="mt-2 break-all text-lg font-black text-slate-800" x-text="newUsername"></p></div>
                        <div class="rounded-xl border border-emerald-200 bg-white p-4"><p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Contraseña temporal</p><p class="mt-2 break-all text-lg font-black text-slate-800" x-text="newPassword"></p></div>
                    </div>
                    <div class="mt-4 rounded-2xl border border-amber-300 bg-amber-100 p-4 text-sm leading-6 text-amber-900" role="alert">
                        <p class="font-black">Importante</p>
                        <p class="mt-1">Comunícale al titular el cambio inmediato de sus credenciales. Al iniciar sesión debe cambiar su usuario y contraseña.</p>
                    </div>
                    <button type="button" @click="closeRecover()" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#4959A6]">Cerrar y ocultar credenciales</button>
                </section>
            </div>
        </div>
    </x-admin-shell>
</x-admin-layout>

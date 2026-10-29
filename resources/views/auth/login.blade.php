@php
    $estudiantePortalUrl = $estudiantePortalUrl ?? null;
    $estudianteQrUrl = $estudianteQrUrl ?? null;
    $loginFailed = $errors->has('username') || $errors->has('password');
    $loginLockoutUntil = $loginLockoutUntil ?? null;
    $loginLockoutSeconds = $loginLockoutUntil ? max(0, $loginLockoutUntil - now()->timestamp) : 0;
@endphp

<x-guest-layout>
    {{-- Credenciales incorrectas o cuenta bloqueada: notificación emergente --}}
    <x-toast-push type="error" :message="$errors->first('username') ?: $errors->first('password')" />

    {{-- Encabezado --}}
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
            Acceso administrativo
        </h2>
        <p class="mt-1.5 text-sm text-slate-500">
            Ingresa con tu cuenta personal.
        </p>
    </div>

    {{-- Formulario de inicio de sesión --}}
    <div
        x-data="{
            lockoutActive: {{ $loginLockoutSeconds > 0 ? 'true' : 'false' }},
            secondsRemaining: {{ $loginLockoutSeconds }},
            timer: null,
            startLockoutTimer() {
                if (!this.lockoutActive) return;
                this.timer = window.setInterval(() => {
                    this.secondsRemaining = Math.max(0, this.secondsRemaining - 1);

                    if (this.secondsRemaining === 0) {
                        window.clearInterval(this.timer);
                        window.location.reload();
                    }
                }, 1000);
            },
            formattedTime() {
                const minutes = Math.floor(this.secondsRemaining / 60).toString().padStart(2, '0');
                const seconds = (this.secondsRemaining % 60).toString().padStart(2, '0');

                return `${minutes}:${seconds}`;
            }
        }"
        x-init="startLockoutTimer()"
        x-cloak
    >
        <div x-show="!lockoutActive">
            <form
                id="login-form"
                method="POST"
                action="{{ route('login') }}"
                class="space-y-4"
                autocomplete="on"
            >
        @csrf

        {{-- Usuario --}}
        <div>
            <label for="username" class="mb-1.5 block text-sm font-semibold text-slate-700">
                Usuario
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </span>
                <input
                    id="username"
                    name="username"
                    type="text"
                    value="{{ old('username', '') }}"
                    placeholder="Ingrese su usuario"
                    required
                    autofocus
                    autocomplete="username"
                    class="block w-full rounded-xl border py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 {{ $loginFailed ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500/10' : 'border-slate-200 bg-slate-50/70 focus:border-blue-500 focus:bg-white focus:ring-blue-500/10' }}"
                >
            </div>
        </div>

        {{-- Contraseña --}}
        <div>
            <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">
                Contraseña
            </label>
            <div class="relative" x-data="{ passwordVisible: false }">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5a4.5 4.5 0 0 0-9 0v3m-.75 0h10.5a1.5 1.5 0 0 1 1.5 1.5v6a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5v-6a1.5 1.5 0 0 1 1.5-1.5Z" />
                    </svg>
                </span>
                <input
                    id="password"
                    name="password"
                    type="password"
                    x-bind:type="passwordVisible ? 'text' : 'password'"
                    placeholder="Ingrese su contraseña"
                    required
                    autocomplete="current-password"
                    class="login-password block w-full rounded-xl border py-3 pl-11 pr-12 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 {{ $loginFailed ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500/10' : 'border-slate-200 bg-slate-50/70 focus:border-blue-500 focus:bg-white focus:ring-blue-500/10' }}"
                >
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-blue-700 focus:outline-none focus-visible:text-blue-700"
                    x-on:click="passwordVisible = !passwordVisible"
                    x-bind:aria-label="passwordVisible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    x-bind:title="passwordVisible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    x-bind:aria-pressed="passwordVisible"
                >
                    {{-- Icono de ojo abierto --}}
                    <svg x-show="!passwordVisible" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    {{-- Icono de ojo cerrado --}}
                    <svg x-show="passwordVisible" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.58 10.59a2 2 0 0 0 2.83 2.83M9.88 5.24A10.8 10.8 0 0 1 12 5.03c6.25 0 9.75 6.97 9.75 6.97a15.7 15.7 0 0 1-4.1 4.72M6.23 6.23C3.7 7.9 2.25 12 2.25 12s3.5 6.75 9.75 6.75c.87 0 1.7-.12 2.48-.34" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Recordarme y recuperar contraseña --}}
        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                    class="h-4 w-4 rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500/30"
                >
                <span>Recordarme</span>
            </label>

            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="text-sm font-semibold text-blue-700 transition hover:text-blue-900 hover:underline"
                >
                    ¿Olvidó su contraseña?
                </a>
            @endif
        </div>

        {{-- Botón de acceso --}}
        <button
            type="submit"
            class="flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 px-4 py-3 text-sm font-bold uppercase tracking-[0.14em] text-white shadow-lg shadow-blue-900/20 transition duration-200 hover:from-blue-700 hover:via-blue-800 hover:to-indigo-800 hover:shadow-xl hover:shadow-blue-900/25 focus:outline-none focus:ring-4 focus:ring-blue-500/30 active:scale-[0.99]"
        >
            INICIAR SESIÓN
        </button>

        {{-- Aviso de bloqueo progresivo --}}
        <div class="flex items-start gap-2.5 rounded-xl bg-slate-50 px-3.5 py-2.5 text-[11px] leading-5 text-slate-500 ring-1 ring-inset ring-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751A11.959 11.959 0 0 1 12 2.714Z" />
            </svg>
            <span>Protección activa: tras varios intentos fallidos, la cuenta se bloquea temporalmente.</span>
        </div>
            </form>
        </div>

        <div
            x-show="lockoutActive"
            class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-8 text-center shadow-sm"
            role="status"
            aria-live="polite"
        >
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM4.5 19.5h15a1.5 1.5 0 0 0 1.299-2.25l-7.5-13a1.5 1.5 0 0 0-2.598 0l-7.5 13A1.5 1.5 0 0 0 4.5 19.5Z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-black text-amber-900">Cuenta bloqueada temporalmente</h3>
            <p class="mt-2 text-sm leading-6 text-amber-800">Se alcanzó el límite de intentos fallidos.</p>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-amber-700">Puedes volver a intentarlo en</p>
            <p class="mt-1 font-mono text-4xl font-black tracking-wider text-amber-900" x-text="formattedTime()"></p>
        </div>
    </div>

    {{-- Acceso al portal de estudiantes: se muestra en el panel lateral --}}
    @if (Route::has('estudiante.login'))
        @push('branding-actions')
            <div x-data>
                <button
                    type="button"
                    class="group flex w-full items-center gap-3 rounded-2xl border border-white/10 bg-white/5 p-3 text-left transition hover:border-cyan-400/30 hover:bg-white/10 focus:outline-none focus:ring-4 focus:ring-cyan-400/20"
                    x-on:click="$dispatch('open-estudiante-qr')"
                    aria-haspopup="dialog"
                    aria-controls="estudiante-qr-modal"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-cyan-400/20 bg-cyan-400/10 text-cyan-300 transition group-hover:bg-cyan-400/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <rect x="3.5" y="3.5" width="6" height="6" rx="1" />
                            <rect x="14.5" y="3.5" width="6" height="6" rx="1" />
                            <rect x="3.5" y="14.5" width="6" height="6" rx="1" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 14.5h2.5v2.5h-2.5zM18.5 14.5h2v2h-2zM14.5 18.5h2v2h-2zM18.5 18.5h2v2h-2z" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-bold text-white">Acceso Estudiantil (Código QR)</span>
                        <span class="mt-0.5 block text-[11px] text-slate-400">Escanea desde una tablet o celular.</span>
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-500 transition group-hover:translate-x-0.5 group-hover:text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
        @endpush
    @endif

    {{-- Limpiar campos al restaurar la página con Atrás --}}
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                const form = document.getElementById('login-form');
                if (form) {
                    form.reset();
                }

                const usuario = document.getElementById('username');
                const contrasena = document.getElementById('password');

                if (usuario) {
                    usuario.value = '';
                }

                if (contrasena) {
                    contrasena.value = '';
                }
            }
        });
    </script>

    @push('modals')
        <div
            id="estudiante-qr-modal"
            x-data="{
                showQr: false,
                reconnecting: false,
                qrMessage: '',
                async reconnectQr() {
                    this.reconnecting = true;
                    this.qrMessage = 'Verificando conexión con Cloudflare Tunnel...';

                    try {
                        const response = await fetch('{{ route('estudiante.qr.reconnect') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                            },
                        });
                        const launch = await response.json();

                        if (launch.available) {
                            window.location.reload();
                            return;
                        }

                        this.qrMessage = launch.message;
                        const deadline = Date.now() + 25000;

                        while (Date.now() < deadline) {
                            await new Promise((resolve) => setTimeout(resolve, 2000));

                            const statusResponse = await fetch('{{ route('estudiante.qr.status') }}', {
                                headers: { 'Accept': 'application/json' },
                            });
                            const status = await statusResponse.json();

                            if (status.available) {
                                window.location.reload();
                                return;
                            }

                            this.qrMessage = status.message;

                            if (! status.starting) {
                                break;
                            }
                        }

                        window.location.reload();
                    } catch (error) {
                        this.qrMessage = 'No se pudo conectar con Cloudflare Tunnel. Intenta nuevamente.';
                    }

                    this.reconnecting = false;
                }
            }"
            x-on:open-estudiante-qr.window="showQr = true"
            x-on:keydown.escape.window="showQr = false"
            x-show="showQr"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="qr-modal-title"
            x-on:click.self="showQr = false"
        >
            <div class="absolute inset-0 bg-slate-950/75 backdrop-blur-sm" x-on:click="showQr = false"></div>

            <div class="relative max-h-[calc(100vh-2rem)] w-full max-w-lg overflow-y-auto rounded-3xl bg-white shadow-2xl ring-1 ring-slate-900/10">
                {{-- Encabezado del modal --}}
                <div class="relative overflow-hidden bg-gradient-to-r from-slate-950 via-blue-950 to-indigo-950 px-6 py-5 text-white sm:px-8">
                    <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-cyan-400/20 blur-3xl" aria-hidden="true"></div>
                    <div class="relative flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-cyan-300">
                                Portal del estudiante
                            </p>
                            <h3 id="qr-modal-title" class="mt-1 text-xl font-bold">
                                Acceso Estudiantil
                            </h3>
                        </div>
                        <button
                            type="button"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-slate-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-cyan-400/40"
                            x-on:click="showQr = false"
                            aria-label="Cerrar"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-6 sm:px-8">
                    @if ($estudianteQrUrl)
                        {{-- QR configurado --}}
                        <div class="mx-auto w-full max-w-[280px] rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                            <img
                                src="{{ $estudianteQrUrl }}"
                                alt="Código QR de acceso al portal estudiantil"
                                class="h-auto w-full rounded-xl"
                            >
                        </div>

                        <p class="mt-4 text-center text-sm font-medium text-slate-700">
                            Apunta con la cámara de tu tablet o celular para entrar al portal.
                        </p>
                        <p class="mt-2 break-all text-center text-[11px] text-slate-400">
                            {{ $estudiantePortalUrl }}
                        </p>

                        <div class="mt-6">
                            <a
                                href="{{ route('estudiante.qr') }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/60 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                Abrir ventana de configuración
                            </a>
                        </div>
                    @else
                        {{-- QR pendiente de configuración --}}
                        <div class="mx-auto flex aspect-square w-full max-w-[280px] flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50 p-6 text-center">
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-300 shadow-sm ring-1 ring-slate-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3.5" y="3.5" width="6" height="6" rx="1" />
                                    <rect x="14.5" y="3.5" width="6" height="6" rx="1" />
                                    <rect x="3.5" y="14.5" width="6" height="6" rx="1" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 14.5h2.5v2.5h-2.5zM18.5 14.5h2v2h-2zM14.5 18.5h2v2h-2zM18.5 18.5h2v2h-2z" />
                                </svg>
                            </span>
                            <span class="mt-4 inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                Túnel pendiente
                            </span>
                            <p class="mt-3 text-sm font-bold text-slate-700">
                                QR no disponible
                            </p>
                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                Conecta el tunel externo y define
                                <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-slate-700">ESTUDIANTE_PORTAL_URL</code>
                                con la URL publica para generar el codigo.
                            </p>
                        </div>

                        <p class="mt-4 text-center text-sm font-medium text-slate-700">
                            El QR se generará cuando el túnel esté conectado y la URL pública esté configurada.
                        </p>

                        <a
                            href="{{ route('estudiante.qr') }}"
                            class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/60 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            Abrir ventana de configuración
                        </a>
                    @endif

                    <div class="mt-5">
                        <p class="mb-3 text-center text-xs leading-5 text-slate-500" x-show="qrMessage" x-text="qrMessage"></p>
                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                            x-on:click="reconnectQr()"
                            x-bind:disabled="reconnecting"
                        >
                            <svg x-show="reconnecting" x-cloak class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span x-text="reconnecting ? 'Reconectando...' : 'Reconectar con el Portal Publico'"></span>
                        </button>
                    </div>

                    <button
                        type="button"
                        class="mt-3 w-full rounded-xl px-4 py-2 text-xs font-semibold text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300"
                        x-on:click="showQr = false"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endpush

    <script>
        async function downloadStyledQr(event, link) {
            event.preventDefault();

            const originalLabel = link.innerHTML;
            link.setAttribute('aria-busy', 'true');
            link.classList.add('pointer-events-none', 'opacity-70');
            link.innerHTML = 'Preparando imagen...';

            try {
                const response = await fetch(link.href, {
                    headers: { 'Accept': 'image/svg+xml' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('No se pudo generar la imagen.');
                }

                const svgBlob = await response.blob();
                const imageUrl = URL.createObjectURL(svgBlob);
                const image = new Image();

                await new Promise((resolve, reject) => {
                    image.onload = resolve;
                    image.onerror = reject;
                    image.src = imageUrl;
                });

                const canvas = document.createElement('canvas');
                canvas.width = 1800;
                canvas.height = 2400;

                const context = canvas.getContext('2d');
                context.fillStyle = '#f8fafc';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(imageUrl);

                const pngBlob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
                const downloadUrl = URL.createObjectURL(pngBlob);
                const downloadLink = document.createElement('a');
                downloadLink.href = downloadUrl;
                downloadLink.download = 'qr-acceso-estudiantil.png';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                downloadLink.remove();
                URL.revokeObjectURL(downloadUrl);
            } catch (error) {
                window.location.href = link.href;
            } finally {
                link.innerHTML = originalLabel;
                link.classList.remove('pointer-events-none', 'opacity-70');
                link.removeAttribute('aria-busy');
            }
        }
    </script>
</x-guest-layout>

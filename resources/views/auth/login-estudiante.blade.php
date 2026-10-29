<x-guest-estudiante-layout>
    @php($loginFailed = $errors->has('username') || $errors->has('password'))
    @php($loginLockoutUntil = $loginLockoutUntil ?? null)
    @php($loginLockoutSeconds = $loginLockoutUntil ? max(0, $loginLockoutUntil - now()->timestamp) : 0)

    {{-- Credenciales incorrectas o cuenta bloqueada: notificación emergente --}}
    <x-toast-push type="error" :message="$errors->first('username') ?: $errors->first('password')" />

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
                id="estudiante-login-form"
                method="POST"
                action="{{ route('estudiante.login.store') }}"
                class="space-y-5"
                autocomplete="on"
            >
        @csrf

        {{-- Usuario / matrícula --}}
        <div>
            <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">
                Usuario o matrícula
            </label>
            <input
                id="username"
                name="username"
                type="text"
                value="{{ old('username') }}"
                placeholder="Ej. 0000ABC"
                required
                autofocus
                autocomplete="username"
                    class="block w-full rounded-xl border px-4 py-3 text-sm text-slate-800 shadow-sm transition {{ $loginFailed ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-300 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/30' }}"
            >
        </div>

        {{-- Contraseña --}}
        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">
                Contraseña
            </label>
            <div class="relative" x-data="{ passwordVisible: false }">
                <input
                    id="password"
                    name="password"
                    type="password"
                    x-bind:type="passwordVisible ? 'text' : 'password'"
                    placeholder="Ingresa tu contraseña"
                    required
                    autocomplete="current-password"
                    class="login-password block w-full rounded-xl border px-4 py-3 pr-12 text-sm text-slate-800 shadow-sm transition {{ $loginFailed ? 'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-300 focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600/30' }}"
                >
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-emerald-700"
                    x-on:click="passwordVisible = !passwordVisible"
                    x-bind:aria-label="passwordVisible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Recordarme / recuperación --}}
        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                    class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-600"
                >
                <span>Recordarme</span>
            </label>

            @if (Route::has('estudiante.password.request'))
                <a
                    href="{{ route('estudiante.password.request') }}"
                    class="text-sm font-semibold text-emerald-700 transition hover:text-emerald-900 hover:underline"
                >
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <button
            type="submit"
            class="flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:from-emerald-700 hover:to-teal-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
        >
            INGRESAR AL PORTAL
        </button>

        <a
            href="{{ route('registro.aspirante.create') }}"
            class="block text-center text-sm font-bold text-emerald-700 transition hover:text-teal-800 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
        >
            ¿Eres nuevo en el sistema? Regístrate aquí
        </a>
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

    {{-- Limpiar campos al restaurar la página con Atrás --}}
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                const form = document.getElementById('estudiante-login-form');
                if (form) {
                    form.reset();
                }
            }
        });
    </script>
</x-guest-estudiante-layout>

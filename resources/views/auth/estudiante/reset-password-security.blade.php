<x-guest-estudiante-layout :showStudentLink="true">
    <x-toast-push type="error" :message="$errors->first('password') ?: $errors->first('password_confirmation')" />

    <div class="mb-6 text-center">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-emerald-700">Acceso recuperado</p>
        <h2 class="mt-2 text-xl font-extrabold tracking-tight text-slate-800">Nueva contraseña</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Establece una contraseña nueva para volver a ingresar a tu portal.</p>
    </div>

    <form method="POST" action="{{ route('estudiante.password.security.reset') }}" class="space-y-4" x-data="{ nuevaVisible: false, confirmacionVisible: false }">
        @csrf
        <div>
            <x-input-label for="password" value="Nueva contraseña" class="text-slate-700" />
            <div class="relative mt-1">
                <x-text-input
                    id="password"
                    class="block h-12 w-full border-slate-300 pr-12 focus:border-emerald-600 focus:ring-emerald-500/20"
                    :class="$errors->has('password') ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-rose-500/20' : ''"
                    type="password"
                    x-bind:type="nuevaVisible ? 'text' : 'password'"
                    name="password"
                    required
                    autofocus
                    autocomplete="new-password"
                />
                <button type="button" @click="nuevaVisible = !nuevaVisible" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-emerald-700" x-bind:aria-label="nuevaVisible ? 'Ocultar nueva contraseña' : 'Mostrar nueva contraseña'" x-bind:title="nuevaVisible ? 'Ocultar nueva contraseña' : 'Mostrar nueva contraseña'" x-bind:aria-pressed="nuevaVisible">
                    <svg x-show="!nuevaVisible" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-cloak x-show="nuevaVisible" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 2.458 12C3.732 16.057 7.523 19 12 19c1.676 0 3.25-.418 4.63-1.157M6.228 6.228A10.451 10.451 0 0 1 12 5c4.478 0 8.268 2.943 9.542 7a10.48 10.48 0 0 1-1.563 3.777M6.228 6.228 3 3m3.228 3.228 3.65 3.65m0 0a3 3 0 1 0 4.243 4.243M9.878 9.878 14.12 14.12M14.12 14.12 21 21" />
                    </svg>
                </button>
            </div>
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmar contraseña" class="text-slate-700" />
            <div class="relative mt-1">
                <x-text-input
                    id="password_confirmation"
                    class="block h-12 w-full border-slate-300 pr-12 focus:border-emerald-600 focus:ring-emerald-500/20"
                    :class="$errors->has('password_confirmation') ? 'border-rose-400 bg-rose-50 focus:border-emerald-500 focus:ring-emerald-500/20' : ''"
                    type="password"
                    x-bind:type="confirmacionVisible ? 'text' : 'password'"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <button type="button" @click="confirmacionVisible = !confirmacionVisible" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-emerald-700" x-bind:aria-label="confirmacionVisible ? 'Ocultar confirmación' : 'Mostrar confirmación'" x-bind:title="confirmacionVisible ? 'Ocultar confirmación' : 'Mostrar confirmación'" x-bind:aria-pressed="confirmacionVisible">
                    <svg x-show="!confirmacionVisible" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-cloak x-show="confirmacionVisible" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 2.458 12C3.732 16.057 7.523 19 12 19c1.676 0 3.25-.418 4.63-1.157M6.228 6.228A10.451 10.451 0 0 1 12 5c4.478 0 8.268 2.943 9.542 7a10.48 10.48 0 0 1-1.563 3.777M6.228 6.228 3 3m3.228 3.228 3.65 3.65m0 0a3 3 0 1 0 4.243 4.243M9.878 9.878 14.12 14.12M14.12 14.12 21 21" />
                    </svg>
                </button>
            </div>
        </div>

        <x-primary-button class="w-full justify-center bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800">Cambiar contraseña</x-primary-button>
    </form>
</x-guest-estudiante-layout>

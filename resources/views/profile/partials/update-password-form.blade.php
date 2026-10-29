@php($passwordUpdated = session('status') === 'password-updated')

<section x-data="{ actualVisible: false, nuevaVisible: false, confirmacionVisible: false }">
    <header class="mb-6 border-b border-slate-100 pb-5">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                <x-icon name="lock" class="h-5 w-5" />
            </span>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600">Seguridad</p>
                <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Cambiar contraseña</h2>
            </div>
        </div>
        <p class="mt-3 text-sm leading-6 text-slate-500">
            Confirma tu contraseña actual para establecer una nueva contraseña segura.
        </p>
    </header>

        <div>
            <x-input-label for="update_password_current_password" value="Contraseña actual" />
            <div class="relative mt-1">
                <x-text-input id="update_password_current_password" name="current_password" type="password" x-bind:type="actualVisible ? 'text' : 'password'" class="block w-full pr-12 {{ $passwordUpdated ? 'border-emerald-400 bg-emerald-50 focus:border-emerald-500 focus:ring-emerald-500/20' : '' }}" autocomplete="current-password" />
                <button type="button" @click="actualVisible = !actualVisible" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-indigo-700" x-bind:aria-label="actualVisible ? 'Ocultar contraseña actual' : 'Mostrar contraseña actual'" x-bind:title="actualVisible ? 'Ocultar contraseña actual' : 'Mostrar contraseña actual'">
                    <x-icon name="eye" x-show="!actualVisible" class="h-5 w-5" />
                    <x-icon name="eye-off" x-cloak x-show="actualVisible" class="h-5 w-5" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nueva contraseña" />
            <div class="relative mt-1">
                <x-text-input id="update_password_password" name="password" type="password" x-bind:type="nuevaVisible ? 'text' : 'password'" class="block w-full pr-12 {{ $passwordUpdated ? 'border-emerald-400 bg-emerald-50 focus:border-emerald-500 focus:ring-emerald-500/20' : '' }}" autocomplete="new-password" />
                <button type="button" @click="nuevaVisible = !nuevaVisible" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-indigo-700" x-bind:aria-label="nuevaVisible ? 'Ocultar nueva contraseña' : 'Mostrar nueva contraseña'" x-bind:title="nuevaVisible ? 'Ocultar nueva contraseña' : 'Mostrar nueva contraseña'">
                    <x-icon name="eye" x-show="!nuevaVisible" class="h-5 w-5" />
                    <x-icon name="eye-off" x-cloak x-show="nuevaVisible" class="h-5 w-5" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar nueva contraseña" />
            <div class="relative mt-1">
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" x-bind:type="confirmacionVisible ? 'text' : 'password'" class="block w-full pr-12 {{ $passwordUpdated ? 'border-emerald-400 bg-emerald-50 focus:border-emerald-500 focus:ring-emerald-500/20' : '' }}" autocomplete="new-password" />
                <button type="button" @click="confirmacionVisible = !confirmacionVisible" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-indigo-700" x-bind:aria-label="confirmacionVisible ? 'Ocultar confirmación' : 'Mostrar confirmación'" x-bind:title="confirmacionVisible ? 'Ocultar confirmación' : 'Mostrar confirmación'">
                    <x-icon name="eye" x-show="!confirmacionVisible" class="h-5 w-5" />
                    <x-icon name="eye-off" x-cloak x-show="confirmacionVisible" class="h-5 w-5" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="hidden flex-col gap-3 border-t border-slate-100 pt-5">
            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5 {{ request()->routeIs('estudiante.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-700 shadow-emerald-900/10 hover:from-emerald-700 hover:to-teal-800' : 'bg-gradient-to-r from-[#1E2A5A] to-[#272F7A] shadow-indigo-950/10 hover:from-[#272F7A] hover:to-[#4959A6]' }}">
                <x-icon name="lock" class="h-4 w-4" />
                Actualizar contraseña
            </button>
        </div>
</section>

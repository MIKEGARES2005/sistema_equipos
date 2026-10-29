<x-guest-estudiante-layout :showStudentLink="true">
    <x-toast-push type="error" :message="$errors->first('username')" />

    <div class="mb-6 text-center">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-emerald-700">Recuperación de acceso</p>
        <h2 class="mt-2 text-xl font-extrabold tracking-tight text-slate-800">
            Recuperar contraseña
        </h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">
            Ingresa tu matrícula o nombre de usuario para continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('estudiante.password.security.verify') }}" class="space-y-4">
        @csrf
        <div>
            <x-input-label for="username" value="Usuario o matrícula" class="text-slate-700" />
            <x-text-input
                id="username"
                class="mt-1 block h-12 w-full max-w-none box-border border-slate-300 focus:border-emerald-600 focus:ring-emerald-500/20"
                :class="$errors->has('username') ? 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-rose-500/20' : ''"
                type="text"
                name="username"
                :value="old('username')"
                required
                autofocus
                autocomplete="username"
            />
        </div>

        <x-primary-button class="h-12 w-full max-w-none justify-center bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800">
            Continuar
        </x-primary-button>
    </form>
</x-guest-estudiante-layout>

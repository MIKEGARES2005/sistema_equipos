<x-guest-layout>
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-800">
            Nueva contraseña
        </h2>
        <p class="text-sm text-gray-600 mt-1">
            Las respuestas fueron verificadas correctamente.
            Ahora puedes establecer una nueva contraseña.
        </p>
    </div>
    <form method="POST" action="{{ route('password.security.reset') }}">
        @csrf
        <div>
            <x-input-label for="password" :value="__('Nueva contraseña')" />
            <x-text-input
                id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label
                for="password_confirmation"
                :value="__('Confirmar nueva contraseña')"
            />
            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
            <x-input-error
                :messages="$errors->get('password_confirmation')"
                class="mt-2"
            />
        </div>
        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Cambiar contraseña
            </x-primary-button>
        </div>
    </form>
    <p class="mt-6 border-t border-gray-200 pt-4 text-center text-xs text-gray-500">
        <a href="{{ route('login') }}" class="font-semibold text-[#272F7A] hover:underline">
            Acceso administrativo / docente
        </a>
    </p>
</x-guest-layout>

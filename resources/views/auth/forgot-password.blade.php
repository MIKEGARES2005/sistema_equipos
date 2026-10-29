<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Ingresa tu nombre de usuario para recuperar el acceso a tu cuenta personal.') }}
    </div>
    <form method="POST" action="{{ route('password.security.verify') }}">
        @csrf
        <div>
            <x-input-label for="username" :value="__('Nombre de usuario')" />
            <x-text-input
                id="username"
                class="block mt-1 w-full"
                type="text"
                name="username"
                :value="old('username')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>
        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Continuar') }}
            </x-primary-button>
        </div>
    </form>
    <p class="mt-6 border-t border-gray-200 pt-4 text-center text-xs text-gray-500">
        <a href="{{ route('login') }}" class="font-semibold text-[#272F7A] hover:underline">
            Acceso administrativo / docente
        </a>
    </p>
</x-guest-layout>

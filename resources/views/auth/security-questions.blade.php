<x-guest-layout>
    <x-toast-push type="error" :message="$errors->first('respuesta')" />

    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-800">
            Preguntas de seguridad
        </h2>
        <p class="text-sm text-gray-600 mt-1">
            Responde correctamente las tres preguntas para recuperar tu contraseña.
        </p>
    </div>
    <form
        method="POST"
        action="{{ route('password.security.answers') }}"
        x-data="{ sending: false, respuestaVisible: [false, false, false] }"
        x-on:submit="if (sending) { $event.preventDefault(); } else { sending = true; }"
    >
        @csrf
        @foreach ($preguntas as $index => $pregunta)
            <div class="mb-4">
                <x-input-label
                    :value="'Pregunta ' . ($index + 1)"
                />
                <p class="mt-1 text-sm font-medium text-gray-700">
                    {{ $pregunta->pregunta }}
                </p>
                <div class="relative mt-2">
                    <x-text-input
                        class="block w-full pr-12"
                        type="password"
                        x-bind:type="respuestaVisible[{{ $index }}] ? 'text' : 'password'"
                        name="respuesta[]"
                        :value="old('respuesta.' . $index)"
                        required
                    />
                    <button type="button" @click="respuestaVisible[{{ $index }}] = !respuestaVisible[{{ $index }}]" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-gray-500 transition hover:text-[#272F7A]" x-bind:aria-label="respuestaVisible[{{ $index }}] ? 'Ocultar respuesta' : 'Mostrar respuesta'">
                        <x-icon name="eye" x-show="!respuestaVisible[{{ $index }}]" class="h-5 w-5" />
                        <x-icon name="eye-off" x-cloak x-show="respuestaVisible[{{ $index }}]" class="h-5 w-5" />
                    </button>
                </div>
            </div>
        @endforeach
        <div class="flex items-center justify-end mt-4">
            <x-primary-button x-bind:disabled="sending">
                Verificar respuestas
            </x-primary-button>
        </div>
    </form>
    <p class="mt-6 border-t border-gray-200 pt-4 text-center text-xs text-gray-500">
        <a href="{{ route('login') }}" class="font-semibold text-[#272F7A] hover:underline">
            Acceso administrativo / docente
        </a>
    </p>
</x-guest-layout>

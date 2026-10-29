<x-guest-estudiante-layout :showStudentLink="true">
    <x-toast-push type="error" :message="$errors->first('respuesta')" />

    <div class="mb-6 text-center">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-emerald-700">Verificación segura</p>
        <h2 class="mt-2 text-xl font-extrabold tracking-tight text-slate-800">
            Preguntas de seguridad
        </h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">
            Responde las tres preguntas para recuperar tu contraseña.
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('estudiante.password.security.answers') }}"
        class="space-y-4"
        x-data="{ sending: false, respuestaVisible: [false, false, false] }"
        x-on:submit="if (sending) { $event.preventDefault(); } else { sending = true; }"
    >
        @csrf
        @foreach ($preguntas as $index => $pregunta)
            <div>
                <x-input-label :value="'Pregunta '.($index + 1)" class="text-slate-700" />
                <p class="mt-1 text-sm font-medium leading-5 text-slate-600">{{ $pregunta->pregunta }}</p>
                <div class="relative mt-2">
                    <x-text-input
                        class="block h-12 w-full border-slate-300 pr-12 focus:border-emerald-600 focus:ring-emerald-500/20"
                        type="password"
                        x-bind:type="respuestaVisible[{{ $index }}] ? 'text' : 'password'"
                        name="respuesta[]"
                        :value="old('respuesta.'.$index)"
                        required
                        autocomplete="off"
                    />
                    <button type="button" @click="respuestaVisible[{{ $index }}] = !respuestaVisible[{{ $index }}]" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-emerald-700" x-bind:aria-label="respuestaVisible[{{ $index }}] ? 'Ocultar respuesta' : 'Mostrar respuesta'">
                        <x-icon name="eye" x-show="!respuestaVisible[{{ $index }}]" class="h-5 w-5" />
                        <x-icon name="eye-off" x-cloak x-show="respuestaVisible[{{ $index }}]" class="h-5 w-5" />
                    </button>
                </div>
            </div>
        @endforeach

        <x-primary-button x-bind:disabled="sending" class="mt-2 w-full justify-center bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 disabled:cursor-not-allowed disabled:opacity-60">
            Verificar respuestas
        </x-primary-button>
    </form>
</x-guest-estudiante-layout>

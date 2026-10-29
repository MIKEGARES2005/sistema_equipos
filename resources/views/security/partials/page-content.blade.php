@php
    $esEstudiante = $user->isEstudiante();
    $rutaPanel = $esEstudiante ? route('estudiante.dashboard') : route('dashboard');
@endphp

<main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] {{ $esEstudiante ? 'text-emerald-600' : 'text-indigo-500' }}">
        <span>{{ $esEstudiante ? 'Portal del estudiante' : 'Administración' }}</span>
        <span class="text-slate-300">/</span>
        <span>Mi cuenta</span>
        <span class="text-slate-300">/</span>
        <span class="text-slate-400">Seguridad</span>
    </div>

    <section class="dashboard-rise relative overflow-hidden rounded-3xl px-6 py-7 text-white shadow-2xl sm:px-8 sm:py-8 {{ $esEstudiante ? 'bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-500 shadow-emerald-950/20' : 'bg-gradient-to-br from-[#0B1226] via-[#1E2A5A] to-[#272F7A] shadow-indigo-950/20' }}">
        @if ($esEstudiante)
            <div class="absolute right-4 top-4 z-10 hidden sm:right-8 sm:top-8 sm:block">
                <a href="{{ route('estudiante.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-white/70 bg-white px-3 py-2 text-xs font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50 sm:px-4 sm:py-2.5 sm:text-sm">
                    Volver al panel
                </a>
            </div>
        @endif
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 left-1/3 h-64 w-64 rounded-full bg-indigo-400/20 blur-3xl" aria-hidden="true"></div>
        <div class="dashboard-grid pointer-events-none absolute inset-0 opacity-30" aria-hidden="true"></div>

        <div class="relative hidden items-start gap-4 sm:flex sm:pr-48">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-cyan-200 ring-1 ring-white/10">
                <x-icon name="shield" class="h-7 w-7" />
            </span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.18em] text-cyan-100">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300 shadow-[0_0_10px_rgba(110,231,183,0.9)]"></span>
                        Seguridad
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-indigo-100">
                        <x-icon name="key" class="h-3.5 w-3.5 text-cyan-200" />
                        3 preguntas
                    </span>
                </div>
                <h1 class="mt-4 text-2xl font-black tracking-tight sm:text-3xl">Preguntas de seguridad</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-indigo-100/80">
                    Configura tres preguntas personales. Solo tú puedes consultar y actualizar las preguntas de tu cuenta.
                </p>
            </div>
        </div>

        <div class="relative z-10 space-y-4 sm:hidden">
            <div class="flex items-start justify-between gap-4">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-cyan-100 ring-1 ring-white/10">
                    <x-icon name="shield" class="h-8 w-8" />
                </span>
                <a href="{{ route('estudiante.dashboard') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl border border-white/80 bg-white px-3 py-2.5 text-xs font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                    Volver al panel
                </a>
            </div>

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-2.5 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] text-cyan-100">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Seguridad
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-2.5 py-1.5 text-[9px] font-bold uppercase tracking-[0.1em] text-indigo-100">
                        <x-icon name="key" class="h-3.5 w-3.5 text-cyan-200" />
                        3 preguntas
                    </span>
                </div>
                <h1 class="mt-3 break-words text-2xl font-black leading-tight tracking-tight">Preguntas de seguridad</h1>
                <p class="mt-2 text-sm leading-6 text-indigo-100/90">
                    Configura tres preguntas personales. Solo tú puedes consultar y actualizar las preguntas de tu cuenta.
                </p>
            </div>
        </div>
    </section>

    @if ($errors->any())
        <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <p class="font-bold">Revisa la información ingresada:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="dashboard-rise overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-5 sm:px-7">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                    <x-icon name="key" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500">Seguridad de la cuenta</p>
                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Configuración personal</h2>
                </div>
            </div>
            <p class="mt-3 text-sm leading-6 text-slate-500">
                Las respuestas se almacenan de forma protegida y nunca se muestran después de guardarlas. Escríbelas de nuevo para cambiar la configuración.
            </p>
        </div>

        <form method="POST" action="{{ $esEstudiante ? route('estudiante.security.questions.update') : route('security.questions.update') }}" class="space-y-5 p-5 sm:p-7" x-data="{
            respuestaVisible: [false, false, false, false],
            formatearPregunta(campo) {
                const texto = campo.value.trim().replace(/^¿+/, '').replace(/\?+$/, '').trim();
                campo.value = texto ? '¿' + texto + '?' : '';
                campo.setSelectionRange(campo.value.length - (texto ? 1 : 0), campo.value.length - (texto ? 1 : 0));
            }
        }">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 ring-1 ring-amber-200">
                        <x-icon name="lock" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-black text-amber-900">Confirma tu contraseña actual</h3>
                        <p class="mt-1 text-xs leading-5 text-amber-800/80">Por seguridad, necesitamos verificar tu contraseña antes de guardar las preguntas.</p>
                        <label for="current_password" class="sr-only">Contraseña actual</label>
                        <div class="relative mt-3">
                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                x-bind:type="respuestaVisible[0] ? 'text' : 'password'"
                                required
                                autocomplete="current-password"
                                class="w-full rounded-xl border-amber-200 bg-white px-4 py-3 pr-12 text-sm text-slate-700 focus:border-amber-400 focus:ring-amber-200"
                            >
                            <button type="button" @click="respuestaVisible[0] = !respuestaVisible[0]" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-amber-700 transition hover:text-amber-900" x-bind:aria-label="respuestaVisible[0] ? 'Ocultar contraseña actual' : 'Mostrar contraseña actual'">
                                <x-icon name="eye" x-show="!respuestaVisible[0]" class="h-5 w-5" />
                                <x-icon name="eye-off" x-cloak x-show="respuestaVisible[0]" class="h-5 w-5" />
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            @for ($i = 0; $i < 3; $i++)
                @php
                    $preguntaEscrita = old('preguntas.' . $i . '.pregunta', $preguntas[$i]->pregunta ?? '');
                    $preguntaEscrita = trim((string) preg_replace('/^[¿]+|[?]+$/u', '', $preguntaEscrita));
                    $preguntaEscrita = $preguntaEscrita !== '' ? '¿'.$preguntaEscrita.'?' : '';
                @endphp
                <fieldset class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
                    <legend class="px-2">
                        <span class="inline-flex items-center gap-2 text-sm font-black text-slate-800">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-100 text-[11px] font-black text-indigo-700">{{ $i + 1 }}</span>
                            Pregunta {{ $i + 1 }}
                        </span>
                    </legend>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="preguntas_{{ $i }}_pregunta" class="mb-2 block text-sm font-semibold text-slate-700">
                                Pregunta personal
                            </label>
                            <input
                                id="preguntas_{{ $i }}_pregunta"
                                name="preguntas[{{ $i }}][pregunta]"
                                type="text"
                                x-on:input="formatearPregunta($event.target)"
                                value="{{ $preguntaEscrita }}"
                                maxlength="255"
                                required
                                class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-indigo-400 focus:ring-indigo-100"
                            >
                            @error('preguntas.' . $i . '.pregunta')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="preguntas_{{ $i }}_respuesta" class="mb-2 block text-sm font-semibold text-slate-700">
                                Respuesta
                            </label>
                            <div class="relative">
                                <input
                                    id="preguntas_{{ $i }}_respuesta"
                                    name="preguntas[{{ $i }}][respuesta]"
                                    type="password"
                                    x-bind:type="respuestaVisible[{{ $i + 1 }}] ? 'text' : 'password'"
                                    maxlength="255"
                                    autocomplete="new-password"
                                    required
                                    class="w-full rounded-xl border-slate-300 px-4 py-3 pr-12 text-sm focus:border-indigo-400 focus:ring-indigo-100"
                                >
                                <button type="button" @click="respuestaVisible[{{ $i + 1 }}] = !respuestaVisible[{{ $i + 1 }}]" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-500 transition hover:text-indigo-700" x-bind:aria-label="respuestaVisible[{{ $i + 1 }}] ? 'Ocultar respuesta' : 'Mostrar respuesta'">
                                    <x-icon name="eye" x-show="!respuestaVisible[{{ $i + 1 }}]" class="h-5 w-5" />
                                    <x-icon name="eye-off" x-cloak x-show="respuestaVisible[{{ $i + 1 }}]" class="h-5 w-5" />
                                </button>
                            </div>
                            @error('preguntas.' . $i . '.respuesta')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>
            @endfor

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ $rutaPanel }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:border-indigo-200 hover:text-indigo-700">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5 {{ $esEstudiante ? 'bg-gradient-to-r from-emerald-600 to-teal-700 shadow-emerald-900/10 hover:from-emerald-700 hover:to-teal-800' : 'bg-gradient-to-r from-[#1E2A5A] to-[#272F7A] shadow-indigo-950/10 hover:from-[#272F7A] hover:to-[#4959A6]' }}">
                    <x-icon name="check" class="h-4 w-4" />
                    Guardar preguntas
                </button>
            </div>
        </form>
    </section>
</main>

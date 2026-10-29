<x-admin-layout>
    <x-admin-shell>
        @php
            $esMultiple = ($credenciales['multiple'] ?? false) === true;
            $cantidad = $esMultiple ? count($credenciales['items'] ?? []) : 1;
        @endphp

        <main class="relative flex min-h-[calc(100vh-5rem)] items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="fixed inset-0 bg-slate-950/45 backdrop-blur-[2px]" aria-hidden="true"></div>

            <section role="dialog" aria-modal="true" aria-labelledby="credenciales-titulo" class="relative z-10 w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-900/10">
                <div class="bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-500 px-6 py-7 text-white sm:px-9">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold ring-1 ring-white/30">✓</span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-100">Proceso completado</p>
                            <h1 id="credenciales-titulo" class="mt-1 text-2xl font-extrabold sm:text-3xl">Perfil y usuario creados correctamente</h1>
                            <p class="mt-2 text-sm leading-6 text-emerald-50">{{ $cantidad }} {{ $cantidad === 1 ? 'registro fue aprobado' : 'registros fueron aprobados' }} y ya puede iniciar sesión en el portal estudiantil.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 px-6 py-7 sm:px-9 sm:py-8">
                    @if ($esMultiple)
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-5 text-center text-sm leading-7 text-emerald-950">
                            <p class="font-extrabold uppercase tracking-wide text-emerald-900">Proceso completado</p>
                            <p class="mt-2">Se registraron correctamente <strong>{{ $cantidad }} solicitudes</strong>.</p>
                            @if (($credenciales['omitidas'] ?? 0) > 0)
                                <p class="mt-2 text-xs text-emerald-800">{{ $credenciales['omitidas'] }} solicitud(es) no pudieron aprobarse porque ya estaban procesadas, duplicadas o presentaron un error.</p>
                            @endif
                        </div>
                    @else
                        <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-5 py-5">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-indigo-700">Credenciales temporales</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-indigo-100 bg-white px-4 py-3">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Nombre de usuario</p>
                                    <p class="mt-1 break-all text-lg font-black text-slate-800">{{ $credenciales['username'] }}</p>
                                </div>
                                <div class="rounded-xl border border-indigo-100 bg-white px-4 py-3">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Contraseña temporal</p>
                                    <p class="mt-1 break-all text-lg font-black text-slate-800">{{ $credenciales['password'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-5 text-sm leading-7 text-amber-950">
                        <p class="font-extrabold uppercase tracking-wide text-amber-900">Aclaración</p>
                        <p class="mt-2">El nombre de usuario es <strong>nombres_apellido_paterno</strong> y la contraseña es <strong>apellido_paterno_ci_matricula</strong>.</p>
                        <p class="mt-2">Indique al usuario que debe cambiar inmediatamente sus credenciales y registrar sus preguntas de seguridad desde su perfil.</p>
                    </div>

                    <p class="text-center text-sm leading-6 text-slate-500">Comunique estos datos únicamente al titular correspondiente. La contraseña temporal no se volverá a mostrar en esta pantalla.</p>

                    <form method="POST" action="{{ route('admin.solicitudes.credentials.clear') }}">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#4959A6]">Cerrar</button>
                    </form>
                </div>
            </section>
        </main>
    </x-admin-shell>
</x-admin-layout>

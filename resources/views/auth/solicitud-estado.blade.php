<x-guest-estudiante-layout :showStudentLink="true">
    <div
        class="text-center"
        x-data="{
            estado: @js($solicitud->estado),
            intervalo: null,
            procesando: false,
            iniciar() {
                if (this.estado === 'pendiente') {
                    this.intervalo = window.setInterval(() => this.consultar(), 5000);
                }
            },
            consultar() {
                fetch(@js(route('registro.aspirante.status.data', $solicitud->token_cancelacion)), { headers: { Accept: 'application/json' } })
                    .then((response) => response.ok ? response.json() : null)
                    .then((data) => {
                        if (!data || data.estado === this.estado || this.procesando) {
                            return;
                        }

                        this.estado = data.estado;

                        if (data.estado === 'aprobada' && data.ingreso_url) {
                            this.procesando = true;
                            window.clearInterval(this.intervalo);
                            return fetch(data.ingreso_url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    Accept: 'application/json',
                                },
                            }).then((response) => response.ok ? response.json() : null);
                        }

                        window.location.reload();
                    })
                    .then((data) => {
                        if (data?.redirect) {
                            window.location.href = data.redirect;
                        }
                    })
                    .catch(() => {});
            }
        }"
        x-init="iniciar()"
    >
        @if ($solicitud->estado === 'pendiente')
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-2xl text-amber-700">⌛</div>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.25em] text-amber-700">Solicitud enviada</p>
            <h2 class="mt-2 text-xl font-extrabold tracking-tight text-slate-800">Estamos revisando tus datos</h2>
            <p class="mt-3 text-sm leading-6 text-slate-500">Tu solicitud está pendiente de aprobación administrativa. Guarda este enlace para consultar su estado.</p>

            <div class="mt-6 rounded-2xl bg-emerald-50 p-4 text-left ring-1 ring-emerald-100">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Solicitante</p>
                <p class="mt-1 font-bold text-slate-800">{{ $solicitud->nombres }} {{ $solicitud->apellido_paterno }}</p>
                <p class="mt-1 text-sm text-slate-600">Matrícula: {{ $solicitud->matricula }}</p>
            </div>

            <form method="POST" action="{{ route('registro.aspirante.cancel', $solicitud->token_cancelacion) }}" class="mt-6">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-rose-200 px-4 py-3 text-sm font-bold text-rose-700 transition hover:bg-rose-50" onclick="return confirm('¿Deseas cancelar esta solicitud?')">
                    Cancelar solicitud
                </button>
            </form>
        @elseif ($solicitud->estado === 'aprobada')
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-2xl text-emerald-700">✓</div>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.25em] text-emerald-700">Solicitud aprobada</p>
            <h2 class="mt-2 text-xl font-extrabold tracking-tight text-slate-800">Tu cuenta ya está disponible</h2>
            <p class="mt-3 text-sm leading-6 text-slate-500">Solicita tus credenciales al administrador y luego ingresa al portal estudiantil.</p>
        @else
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-500">i</div>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.25em] text-slate-500">Solicitud {{ $solicitud->estado }}</p>
            <h2 class="mt-2 text-xl font-extrabold tracking-tight text-slate-800">Esta solicitud ya no está pendiente</h2>
            <p class="mt-3 text-sm leading-6 text-slate-500">Puedes volver al portal estudiantil para iniciar sesión o enviar una nueva solicitud.</p>
        @endif
    </div>
</x-guest-estudiante-layout>

<x-admin-layout>
    <main class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <a href="{{ route('accesorios.index') }}" class="text-sm font-semibold text-[#4959A6] hover:underline">← Volver a accesorios</a>
                    <p class="mt-4 text-sm font-semibold text-[#4959A6]">Inventario / Accesorios / {{ $accesorio->nombre }}</p>
                    <h1 class="mt-1 text-3xl font-bold text-slate-800">Detalle del accesorio</h1>
                </div>
                @if (auth()->user()->isAdministrador())
                    <div class="flex gap-2">
                        <a href="{{ route('accesorios.edit', $accesorio->id) }}" class="rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-[#272F7A] hover:bg-indigo-50">Editar accesorio</a>
                        <form action="{{ route('accesorios.destroy', $accesorio->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este accesorio?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100">Eliminar</button>
                        </form>
                    </div>
                @endif
            </header>

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="overflow-hidden rounded-3xl border border-indigo-100 bg-white shadow-sm">
                <div class="grid lg:grid-cols-[1.05fr_1fr]">
                    <div class="min-h-72 bg-gradient-to-br from-indigo-100 via-blue-50 to-slate-100">
                        @if ($accesorio->imagen_referencial)
                            <img src="{{ asset('storage/'.$accesorio->imagen_referencial) }}" alt="Foto de {{ $accesorio->nombre }}" class="h-full min-h-72 w-full object-cover">
                        @elseif ($accesorio->equipo?->imagen_referencial)
                            <img src="{{ asset('storage/'.$accesorio->equipo->imagen_referencial) }}" alt="Equipo asociado: {{ $accesorio->equipo->codigo }}" class="h-full min-h-72 w-full object-cover">
                        @else
                            <div class="flex h-full min-h-72 items-center justify-center text-7xl text-[#4959A6]" aria-hidden="true">⌁</div>
                        @endif
                    </div>
                    <div class="space-y-6 p-6 sm:p-8">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ strtolower($accesorio->estado) === 'disponible' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $accesorio->estado }}</span>
                            <span class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-extrabold text-[#272F7A]">Cantidad: {{ $accesorio->cantidad }}</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">{{ $accesorio->nombre }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $accesorio->marca ?: 'Marca sin registrar' }} · {{ $accesorio->modelo ?: 'Modelo sin registrar' }}</p>
                        </div>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            @foreach ([
                                'Número de serie' => $accesorio->numero_serie ?: 'No registrado',
                                'Equipo asociado' => $accesorio->equipo?->codigo ?? 'No disponible',
                                'Marca del equipo' => $accesorio->equipo?->marca ?? 'No disponible',
                                'Modelo del equipo' => $accesorio->equipo?->modelo ?? 'No disponible',
                                'Registrado' => $accesorio->created_at?->format('d/m/Y H:i') ?? '—',
                                'Última actualización' => $accesorio->updated_at?->format('d/m/Y H:i') ?? '—',
                            ] as $etiqueta => $valor)
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $etiqueta }}</dt>
                                    <dd class="mt-1 break-words text-sm font-semibold text-slate-800">{{ $valor }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        @if ($accesorio->equipo)
                            <a href="{{ route('equipos.show', $accesorio->equipo->id) }}" class="inline-flex rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-[#272F7A] hover:bg-indigo-100">Ver equipo asociado</a>
                        @endif
                    </div>
                </div>
                @if ($accesorio->observaciones)
                    <div class="border-t border-slate-100 bg-slate-50/70 px-6 py-5 sm:px-8">
                        <h3 class="text-sm font-bold text-slate-700">Observaciones</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $accesorio->observaciones }}</p>
                    </div>
                @endif
            </section>
        </div>
    </main>
</x-admin-layout>

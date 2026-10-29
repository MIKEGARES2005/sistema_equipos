<x-admin-layout>
    <main class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <a href="{{ route('equipos.index') }}" class="text-sm font-semibold text-[#4959A6] hover:underline">← Volver a equipos</a>
                    <p class="mt-4 text-sm font-semibold text-[#4959A6]">Inventario / Equipos / {{ $equipo->codigo }}</p>
                    <h1 class="mt-1 text-3xl font-bold text-slate-800">Detalle del equipo</h1>
                </div>
                @if (auth()->user()->isAdministrador())
                    <div class="flex gap-2">
                        <a href="{{ route('equipos.edit', $equipo->id) }}" class="rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-[#272F7A] hover:bg-indigo-50">Editar equipo</a>
                        <form action="{{ route('equipos.destroy', $equipo->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este equipo?')">
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
                        @if ($equipo->imagen_referencial)
                            <img src="{{ asset('storage/'.$equipo->imagen_referencial) }}" alt="Foto de {{ $equipo->codigo }}" class="h-full min-h-72 w-full object-cover">
                        @else
                            <div class="flex h-full min-h-72 items-center justify-center text-7xl text-[#4959A6]" aria-hidden="true">▣</div>
                        @endif
                    </div>
                    <div class="space-y-6 p-6 sm:p-8">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-extrabold uppercase tracking-wider text-[#272F7A]">{{ $equipo->codigo }}</span>
                                <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ strtolower($equipo->estado) === 'disponible' ? 'bg-emerald-50 text-emerald-700' : (strtolower($equipo->estado) === 'en uso' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">{{ $equipo->estado }}</span>
                            </div>
                            <h2 class="mt-4 text-2xl font-bold text-slate-800">{{ $equipo->marca }} {{ $equipo->modelo }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $equipo->tipo_equipo }} · Uso {{ $equipo->modo_uso }}</p>
                        </div>
                        <dl class="grid gap-4 sm:grid-cols-2">
                            @foreach ([
                                'Número de serie' => $equipo->numero_serie ?: 'No registrado',
                                'Dirección MAC' => $equipo->mac ?: 'No registrada',
                                'Modo de uso' => ucfirst($equipo->modo_uso),
                                'Última conexión' => $equipo->ultima_conexion?->format('d/m/Y H:i') ?? 'Sin conexión registrada',
                                'Registrado' => $equipo->created_at?->format('d/m/Y H:i') ?? '—',
                                'Última actualización' => $equipo->updated_at?->format('d/m/Y H:i') ?? '—',
                            ] as $etiqueta => $valor)
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $etiqueta }}</dt>
                                    <dd class="mt-1 break-words text-sm font-semibold text-slate-800">{{ $valor }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-indigo-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Accesorios relacionados</h2>
                        <p class="mt-1 text-sm text-slate-500">Accesorios vinculados a este equipo.</p>
                    </div>
                    @if (auth()->user()->isAdministrador())
                        <a href="{{ route('accesorios.create', ['equipo_id' => $equipo->id]) }}" class="rounded-lg bg-[#272F7A] px-3 py-2 text-xs font-semibold text-white hover:bg-[#4959A6]">Añadir accesorio</a>
                    @endif
                </div>
                @forelse ($equipo->accesorios as $accesorio)
                    <a href="{{ route('accesorios.show', $accesorio->id) }}" class="mt-4 flex items-center justify-between gap-4 rounded-xl border border-slate-100 bg-slate-50 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/60">
                        <span>
                            <span class="block font-semibold text-slate-800">{{ $accesorio->nombre }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $accesorio->marca ?: 'Sin marca' }} · Cantidad: {{ $accesorio->cantidad }}</span>
                        </span>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $accesorio->estado }}</span>
                    </a>
                @empty
                    <p class="mt-4 rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500">Este equipo todavía no tiene accesorios vinculados.</p>
                @endforelse
            </section>
        </div>
    </main>
</x-admin-layout>

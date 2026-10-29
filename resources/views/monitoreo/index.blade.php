<x-admin-layout>
    <main class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70">
        <div class="mx-auto max-w-7xl space-y-7 px-4 py-8 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#4959A6]">Actividad y monitoreo</p>
                    <h1 class="mt-1 text-3xl font-bold text-slate-800">Monitoreo de equipos</h1>
                    <p class="mt-2 max-w-3xl text-sm text-slate-500">Últimas métricas recibidas desde las computadoras y tablets del laboratorio.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-semibold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Volver al dashboard</a>
            </header>

            <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-900">
                Un equipo se considera conectado si reportó durante los últimos {{ \App\Models\Heartbeat::ONLINE_TIMEOUT_MINUTES }} minutos. Si nunca envió información, se muestra como “Sin datos”.
            </div>

            <section aria-label="Resumen del monitoreo" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Equipos registrados</p>
                    <p class="mt-3 text-3xl font-bold text-[#272F7A]">{{ $estadisticas['total'] }}</p>
                </article>
                <article class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Conectados</p>
                    <p class="mt-3 text-3xl font-bold text-emerald-700">{{ $estadisticas['conectados'] }}</p>
                    <p class="mt-2 text-xs text-slate-500">Heartbeat reciente</p>
                </article>
                <article class="rounded-2xl border border-red-100 bg-gradient-to-br from-white to-red-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Desconectados</p>
                    <p class="mt-3 text-3xl font-bold text-red-700">{{ $estadisticas['desconectados'] }}</p>
                    <p class="mt-2 text-xs text-slate-500">Sin comunicación reciente</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">Sin datos</p>
                    <p class="mt-3 text-3xl font-bold text-slate-700">{{ $estadisticas['sin_datos'] }}</p>
                    <p class="mt-2 text-xs text-slate-500">Aún no han reportado</p>
                </article>
            </section>

            <section class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-6">
                <form method="GET" action="{{ route('monitoreo.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_15rem_auto_auto] lg:items-end">
                    <div>
                        <label for="buscar" class="mb-2 block text-sm font-semibold text-slate-700">Buscar equipo</label>
                        <input id="buscar" name="buscar" type="search" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Código, marca, modelo o serie" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="estado" class="mb-2 block text-sm font-semibold text-slate-700">Conexión</label>
                        <select id="estado" name="estado" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos los estados</option>
                            <option value="conectados" @selected(($filtros['estado'] ?? '') === 'conectados')>Conectados</option>
                            <option value="desconectados" @selected(($filtros['estado'] ?? '') === 'desconectados')>Desconectados</option>
                            <option value="sin_datos" @selected(($filtros['estado'] ?? '') === 'sin_datos')>Sin datos</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#272F7A] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#4959A6]">Filtrar</button>
                    @if (($filtros['buscar'] ?? '') !== '' || ($filtros['estado'] ?? '') !== '')
                        <a href="{{ route('monitoreo.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Limpiar</a>
                    @endif
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-indigo-50 bg-gradient-to-r from-indigo-50 to-blue-50/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800">Estado y métricas por equipo</h2>
                        <p class="mt-1 text-xs text-slate-500">Los valores corresponden al heartbeat más reciente de cada equipo.</p>
                    </div>
                    <p class="text-xs text-slate-500">Último reporte general:
                        <span class="font-semibold text-slate-700">{{ $ultimaActualizacion?->format('d/m/Y H:i:s') ?? 'Sin datos registrados' }}</span>
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Equipo</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Conexión</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">CPU / RAM</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Almacenamiento</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Batería / Temperatura</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Último recibido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($equipos as $equipo)
                                @php
                                    $estadoEstilo = match ($equipo->estado_conexion) {
                                        'conectado' => 'bg-emerald-50 text-emerald-700',
                                        'desconectado' => 'bg-red-50 text-red-700',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                    $estadoEtiqueta = match ($equipo->estado_conexion) {
                                        'conectado' => 'Conectado',
                                        'desconectado' => 'Desconectado',
                                        default => 'Sin datos',
                                    };
                                    $heartbeat = $equipo->ultimoHeartbeat;
                                @endphp
                                <tr class="align-top">
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <a href="{{ route('equipos.show', $equipo->id) }}" class="font-bold text-[#272F7A] hover:underline">{{ $equipo->codigo }}</a>
                                        <p class="mt-1 text-sm text-slate-600">{{ $equipo->marca }} {{ $equipo->modelo }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $estadoEstilo }}">{{ $estadoEtiqueta }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                        <p>CPU: <span class="font-semibold">{{ $heartbeat?->cpu_uso !== null ? $heartbeat->cpu_uso.'%' : '—' }}</span></p>
                                        <p class="mt-1">RAM: <span class="font-semibold">{{ $heartbeat?->ram_uso !== null ? $heartbeat->ram_uso.'%' : '—' }}</span></p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-700">{{ $heartbeat?->almacenamiento_uso !== null ? $heartbeat->almacenamiento_uso.'%' : '—' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-700">
                                        <p>Batería: <span class="font-semibold">{{ $heartbeat?->bateria !== null ? $heartbeat->bateria.'%' : '—' }}</span></p>
                                        <p class="mt-1">Temp.: <span class="font-semibold">{{ $heartbeat?->temperatura !== null ? $heartbeat->temperatura.' °C' : '—' }}</span></p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $heartbeat?->created_at?->format('d/m/Y H:i:s') ?? 'Sin reportes' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">
                                        {{ ($filtros['buscar'] ?? '') !== '' || ($filtros['estado'] ?? '') !== '' ? 'No hay equipos que coincidan con los filtros.' : 'Aún no hay equipos registrados.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">{{ $equipos->links() }}</div>
            </section>
        </div>
    </main>
</x-admin-layout>

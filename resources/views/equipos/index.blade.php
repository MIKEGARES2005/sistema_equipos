<x-admin-layout>
    <main class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70">
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="mt-4 text-sm font-semibold text-[#4959A6]">Inventario / Equipos</p>
                    <h1 class="mt-1 text-3xl font-bold text-slate-800">Equipos</h1>
                    <p class="mt-2 text-sm text-slate-500">Busca y consulta el detalle del equipo registrado.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-semibold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Volver al dashboard</a>
                    @if (auth()->user()->isAdministrador())
                        <a href="{{ route('equipos.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#4959A6]">Registrar equipo</a>
                    @endif
                </div>
            </header>

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="rounded-2xl border border-indigo-100 bg-white p-4 shadow-sm sm:p-5">
                <form method="GET" action="{{ route('equipos.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <label for="buscar" class="sr-only">Buscar equipos</label>
                    <input id="buscar" name="buscar" type="search" value="{{ request('buscar') }}" placeholder="Buscar por código, tipo, marca, modelo, serie o estado..." class="min-h-11 flex-1 rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="min-h-11 rounded-xl bg-[#272F7A] px-5 text-sm font-semibold text-white transition hover:bg-[#4959A6]">Buscar</button>
                    @if (request()->filled('buscar'))
                        <a href="{{ route('equipos.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Limpiar</a>
                    @endif
                </form>
            </section>

            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($equipos as $equipo)
                    <article class="group overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-lg">
                        <a href="{{ route('equipos.show', $equipo->id) }}" class="block">
                            @if ($equipo->imagen_referencial)
                                <img src="{{ asset('storage/'.$equipo->imagen_referencial) }}" alt="Imagen de {{ $equipo->codigo }}" class="h-48 w-full bg-slate-100 object-cover">
                            @else
                                <div class="flex h-48 items-center justify-center bg-gradient-to-br from-indigo-100 via-blue-50 to-slate-100 text-5xl text-[#4959A6]" aria-hidden="true">▣</div>
                            @endif
                            <div class="space-y-3 p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-[#4959A6]">{{ $equipo->codigo }}</p>
                                        <h2 class="mt-1 text-lg font-bold text-slate-800 group-hover:text-[#272F7A]">{{ $equipo->marca }} {{ $equipo->modelo }}</h2>
                                    </div>
                                    <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-bold {{ strtolower($equipo->estado) === 'disponible' ? 'bg-emerald-50 text-emerald-700' : (strtolower($equipo->estado) === 'en uso' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">{{ $equipo->estado }}</span>
                                </div>
                                <p class="text-sm text-slate-500">{{ $equipo->tipo_equipo }} · Uso {{ $equipo->modo_uso }}</p>
                            </div>
                        </a>
                        @if (auth()->user()->isAdministrador())
                            <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
                                <a href="{{ route('equipos.edit', $equipo->id) }}" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-semibold text-[#272F7A] hover:bg-indigo-100">Editar</a>
                                <form action="{{ route('equipos.destroy', $equipo->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este equipo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">Eliminar</button>
                                </form>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-indigo-200 bg-white px-6 py-14 text-center sm:col-span-2 xl:col-span-3">
                        <p class="text-lg font-bold text-slate-700">{{ request()->filled('buscar') ? 'No encontramos equipos con esa búsqueda' : 'Aún no hay equipos registrados' }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ request()->filled('buscar') ? 'Prueba otro código, marca o estado.' : 'Los equipos que registres aparecerán en esta lista.' }}</p>
                    </div>
                @endforelse
            </section>

            <div>{{ $equipos->links() }}</div>
        </div>
    </main>
</x-admin-layout>

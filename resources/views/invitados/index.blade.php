<x-admin-layout>
    <x-admin-shell>
        <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-500">Administración / Invitados</p><h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Invitados</h1><p class="mt-1 text-sm text-slate-500">Administra las personas invitadas y su estado de acceso a recursos.</p></div>
                <a href="{{ route('invitados.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-r from-[#1E2A5A] to-[#272F7A] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:from-[#272F7A] hover:to-[#4959A6]">Crear invitado</a>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
                <form action="{{ route('invitados.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1"><label for="buscar" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Buscar invitado</label><input id="buscar" type="text" name="buscar" value="{{ $buscar }}" placeholder="Nombre, apellido, CI o procedencia..." class="w-full rounded-xl border-slate-300 px-4 py-2.5 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]"></div>
                    <div class="sm:w-44"><label for="estado" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Estado</label><select id="estado" name="estado" class="w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm font-semibold text-slate-700 focus:border-[#4959A6] focus:ring-[#4959A6]"><option value="todos" @selected($estado === 'todos')>Todos</option><option value="activo" @selected($estado === 'activo')>Activos</option><option value="inactivo" @selected($estado === 'inactivo')>Inactivos</option></select></div>
                    <div class="flex gap-2"><button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#272F7A] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#4959A6]">Filtrar</button><a href="{{ route('invitados.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Limpiar</a></div>
                </form>
            </section>

            <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @if ($invitados->isEmpty())
                    <div class="px-6 py-16 text-center"><p class="text-lg font-black text-slate-700">No se encontraron invitados</p><p class="mt-2 text-sm text-slate-500">Prueba con otro criterio de búsqueda.</p></div>
                @else
                    <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200"><thead class="bg-slate-50"><tr><th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Invitado</th><th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Tipo</th><th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">CI</th><th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Estado</th><th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Acciones</th></tr></thead><tbody class="divide-y divide-slate-100">
                        @foreach ($invitados as $invitado)
                            <tr class="hover:bg-slate-50/80"><td class="px-5 py-4"><p class="font-bold text-slate-800">{{ $invitado->nombreCompleto() }}</p><p class="mt-1 text-xs text-slate-400">{{ $invitado->procedencia ?: 'Procedencia no registrada' }}</p></td><td class="px-5 py-4 text-sm font-semibold capitalize text-slate-600">{{ $invitado->tipo }}</td><td class="px-5 py-4 text-sm text-slate-600">{{ trim($invitado->ci.' '.($invitado->ci_extension ?? '')) }}</td><td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $invitado->estado === 'activo' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($invitado->estado) }}</span></td><td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('invitados.show', $invitado) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-[#272F7A] hover:bg-indigo-50">Ver</a><a href="{{ route('invitados.edit', $invitado) }}" class="rounded-lg bg-[#272F7A] px-3 py-2 text-xs font-bold text-white hover:bg-[#4959A6]">Editar</a></div></td></tr>
                        @endforeach
                    </tbody></table></div>
                    @if ($invitados->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $invitados->links() }}</div>@endif
                @endif
            </section>
        </main>
    </x-admin-shell>
</x-admin-layout>

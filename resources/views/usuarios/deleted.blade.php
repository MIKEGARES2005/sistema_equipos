<x-admin-layout>
    <x-admin-shell>
        <div class="min-h-screen bg-[#F4F7FC] text-slate-800">
            <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0B1226] via-[#1E2A5A] to-[#272F7A] px-6 py-7 text-white shadow-2xl shadow-indigo-950/20 sm:px-8 sm:py-8">
                    <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-rose-400/10 blur-3xl" aria-hidden="true"></div>
                    <div class="dashboard-grid pointer-events-none absolute inset-0 opacity-30" aria-hidden="true"></div>
                    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-cyan-200">Administración / Usuarios / Archivo</p>
                            <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">Perfiles eliminados</h1>
                            <p class="mt-2 text-sm leading-6 text-indigo-100/80">Consulta las cuentas archivadas mediante eliminación lógica.</p>
                        </div>
                        <a href="{{ route('usuarios.index') }}" class="relative inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-bold text-[#272F7A] shadow-sm transition hover:bg-indigo-50 hover:text-[#4959A6]">
                            Volver al listado
                        </a>
                    </div>
                </header>

                <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                    @if ($usuarios->isEmpty())
                        <div class="px-6 py-16 text-center">
                            <p class="text-lg font-black text-slate-700">No hay perfiles eliminados</p>
                            <p class="mt-2 text-sm text-slate-500">Los perfiles archivados aparecerán aquí.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Perfil</th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Usuario</th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Tipo</th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Archivado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($usuarios as $usuario)
                                        @php
                                            $perfil = $usuario->tipo_usuario === 'estudiante' ? $usuario->estudiante : $usuario->docente;
                                            $nombre = trim(collect([$perfil?->nombres, $perfil?->apellido_paterno, $perfil?->apellido_materno])->filter()->implode(' '));
                                        @endphp
                                        <tr class="hover:bg-slate-50/80">
                                            <td class="px-5 py-4"><p class="font-bold text-slate-800">{{ $nombre ?: 'Perfil sin nombre' }}</p><p class="mt-1 text-xs text-slate-400">ID #{{ $usuario->id }}</p></td>
                                            <td class="px-5 py-4 font-semibold text-slate-700">{{ $usuario->username }}</td>
                                            <td class="px-5 py-4"><span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold capitalize text-rose-700">{{ $usuario->tipo_usuario }}</span></td>
                                            <td class="px-5 py-4 text-sm text-slate-500">{{ $usuario->deleted_at?->format('d/m/Y H:i') ?? 'No disponible' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($usuarios->hasPages())
                            <div class="border-t border-slate-100 px-5 py-4">{{ $usuarios->links() }}</div>
                        @endif
                    @endif
                </section>
            </main>
        </div>
    </x-admin-shell>
</x-admin-layout>

<x-admin-layout>
    <x-admin-shell>
        <div class="min-h-screen bg-[#F4F7FC] text-slate-800">
            <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <header class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0B1226] via-[#1E2A5A] to-[#272F7A] px-6 py-7 text-white shadow-2xl shadow-indigo-950/20 sm:px-8 sm:py-8">
                    <div class="dashboard-grid pointer-events-none absolute inset-0 opacity-30" aria-hidden="true"></div>
                    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div><p class="text-sm font-medium text-cyan-200">Administración / Invitados</p><h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">Editar invitado</h1><p class="mt-2 text-sm leading-6 text-indigo-100/80">Actualiza los datos personales y el estado del invitado.</p></div>
                        <a href="{{ route('invitados.show', $invitado) }}" class="relative inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-bold text-[#272F7A] shadow-sm transition hover:bg-indigo-50">Volver a la ficha</a>
                    </div>
                </header>
                @include('invitados._form', ['formAction' => route('invitados.update', $invitado)])
            </main>
        </div>
    </x-admin-shell>
</x-admin-layout>

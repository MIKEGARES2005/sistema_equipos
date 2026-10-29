<x-admin-layout>
    <div class="min-h-screen bg-slate-50" x-data="{ menuAbierto: '', menuMovil: false }">
        <div class="flex min-h-screen">
            <aside class="hidden w-72 shrink-0 flex-col bg-[#272F7A] text-white md:flex">
                <div class="border-b border-white/10 px-6 py-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-2xl shadow-inner" aria-hidden="true">💻</div>
                        <div>
                            <p class="text-sm font-bold tracking-wide">SISTEMA DE GESTIÓN</p>
                            <p class="mt-1 text-xs text-indigo-200">Panel docente</p>
                        </div>
                    </div>
                </div>
                <nav aria-label="Navegación docente" class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
                    @include('components.docente-navigation')
                </nav>
                <div class="border-t border-white/10 px-5 py-4">
                    <p class="text-xs font-medium text-indigo-100">Sistema local de laboratorio</p>
                    <p class="mt-1 text-xs text-indigo-200/70">ITIBB · Informática Industrial</p>
                </div>
            </aside>

            <main class="min-w-0 flex-1">
                <header class="border-b border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-3.5 sm:px-8">
                        <div class="flex items-center gap-3">
                            <button type="button" @click="menuMovil = !menuMovil" :aria-expanded="menuMovil" aria-label="Abrir navegación" class="rounded-xl border border-slate-200 px-3 py-2 text-[#272F7A] md:hidden">☰</button>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Panel de control</p>
                                <h1 class="text-lg font-bold tracking-tight text-slate-800 sm:text-xl">Panel docente</h1>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <span class="hidden lg:inline">{{ auth()->user()->name }}</span>
                                <span>Mi cuenta</span>
                            </a>
                            <a href="{{ route('security.questions.edit') }}" class="inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Preguntas de seguridad</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100">Cerrar sesión</button>
                            </form>
                        </div>
                    </div>
                    <nav x-cloak x-show="menuMovil" x-transition aria-label="Navegación móvil docente" class="max-h-[70vh] space-y-1 overflow-y-auto bg-[#272F7A] px-3 py-4 text-white md:hidden">
                        @include('components.docente-navigation')
                    </nav>
                </header>
                <div class="space-y-8 p-4 sm:p-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</x-admin-layout>

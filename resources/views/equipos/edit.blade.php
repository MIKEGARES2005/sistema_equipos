<x-admin-layout>
    <main class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70 px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('equipos.show', $equipo->id) }}" class="text-sm font-semibold text-[#4959A6] hover:underline">← Volver al detalle</a>
            <p class="mt-4 text-sm font-semibold text-[#4959A6]">Inventario / Equipos / {{ $equipo->codigo }}</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-800">Editar equipo</h1>
            <p class="mt-2 text-sm text-slate-500">Actualiza la información registrada del equipo.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('equipos.update', $equipo->id) }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5 rounded-2xl border border-indigo-100 bg-white p-6 shadow-sm sm:p-8">
                @csrf
                @method('PUT')
                @include('equipos._form')
                <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('equipos.show', $equipo->id) }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                    <button type="submit" class="rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white hover:bg-[#4959A6]">Guardar cambios</button>
                </div>
            </form>
        </div>
    </main>
</x-admin-layout>

<div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
        <div class="relative h-28 w-28 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 ring-4 ring-slate-50">
            @if ($fotoActualDisponible)
                <img
                    x-show="!photoPreview"
                    src="{{ request()->routeIs('estudiante.*') ? route('estudiante.profile.photo') : route('profile.photo') }}"
                    alt="Fotografía personal actual"
                    class="h-full w-full object-cover"
                >
            @else
                <div x-show="!photoPreview" class="flex h-full w-full flex-col items-center justify-center gap-1 text-slate-400">
                    <x-icon name="user" class="h-8 w-8" />
                    <span class="text-[10px] font-bold uppercase tracking-wider">Sin foto</span>
                </div>
            @endif

            <img
                x-cloak
                x-show="photoPreview"
                :src="photoPreview"
                alt="Vista previa de la nueva fotografía"
                class="h-full w-full object-cover"
            >
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-sm font-black text-slate-800">Actualiza tu fotografía</p>
            <p class="mt-1 text-xs leading-5 text-slate-500">
                Formatos JPG, PNG o WEBP. Tamaño máximo 2 MB. Se mostrará en tu perfil y en el panel.
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <label
                    for="foto_personal"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:-translate-y-0.5 {{ request()->routeIs('estudiante.*') ? 'bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800' : 'bg-gradient-to-r from-[#1E2A5A] to-[#272F7A] hover:from-[#272F7A] hover:to-[#4959A6]' }}"
                >
                    <x-icon name="plus" class="h-4 w-4" />
                    Elegir archivo
                </label>

                <span class="min-w-0 truncate text-xs text-slate-500" x-text="photoName || 'Ningún archivo seleccionado'">
                    Ningún archivo seleccionado
                </span>
            </div>

            <input
                id="foto_personal"
                name="foto_personal"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                x-on:change="previewPhoto($event)"
                class="sr-only"
            >
        </div>
    </div>

    <x-input-error class="mt-2" :messages="$errors->get('foto_personal')" />
</div>

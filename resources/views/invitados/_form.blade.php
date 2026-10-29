@php
    $esEdicion = isset($invitado) && $invitado?->exists;
@endphp

@if ($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-bold">Revisa los datos del formulario.</p>
        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $formAction }}" class="space-y-6">
    @csrf
    @if ($esEdicion)
        @method('PUT')
    @endif

    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-6">
            <h2 class="text-lg font-black text-slate-800">Datos personales</h2>
            <p class="mt-1 text-sm text-slate-500">Información básica de la persona invitada.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label for="tipo" class="mb-2 block text-sm font-semibold text-slate-700">Tipo de invitado</label>
                <select id="tipo" name="tipo" required class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                    @foreach (['estudiante' => 'Estudiante', 'docente' => 'Docente', 'externo' => 'Externo'] as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(old('tipo', $invitado?->tipo ?? 'externo') === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="nombres" class="mb-2 block text-sm font-semibold text-slate-700">Nombres</label>
                <input id="nombres" name="nombres" value="{{ old('nombres', $invitado?->nombres) }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
            </div>
            <div>
                <label for="apellido_paterno" class="mb-2 block text-sm font-semibold text-slate-700">Apellido paterno</label>
                <input id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $invitado?->apellido_paterno) }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
            </div>
            <div>
                <label for="apellido_materno" class="mb-2 block text-sm font-semibold text-slate-700">Apellido materno</label>
                <input id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno', $invitado?->apellido_materno) }}" maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
            </div>
            <div>
                <label for="ci" class="mb-2 block text-sm font-semibold text-slate-700">Carnet de identidad</label>
                <input id="ci" name="ci" value="{{ old('ci', $invitado?->ci) }}" required maxlength="20" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
            </div>
            <div>
                <label for="ci_extension" class="mb-2 block text-sm font-semibold text-slate-700">Extensión del CI</label>
                <select id="ci_extension" name="ci_extension" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                    <option value="">Sin extensión</option>
                    @foreach (['LP' => 'La Paz', 'OR' => 'Oruro', 'PT' => 'Potosí', 'CB' => 'Cochabamba', 'SC' => 'Santa Cruz', 'TJ' => 'Tarija', 'CH' => 'Chuquisaca', 'BE' => 'Beni', 'PD' => 'Pando'] as $extension => $departamento)
                        <option value="{{ $extension }}" @selected(old('ci_extension', $invitado?->ci_extension) === $extension)>{{ $departamento }} ({{ $extension }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="telefono" class="mb-2 block text-sm font-semibold text-slate-700">Teléfono</label>
                <input id="telefono" name="telefono" value="{{ old('telefono', $invitado?->telefono) }}" maxlength="20" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
            </div>
            <div>
                <label for="semestre" class="mb-2 block text-sm font-semibold text-slate-700">Semestre</label>
                <select id="semestre" name="semestre" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                    <option value="">No aplica</option>
                    @foreach (range(1, 4) as $semestre)
                        <option value="{{ $semestre }}" @selected((string) old('semestre', $invitado?->semestre) === (string) $semestre)>{{ $semestre }}° semestre</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="procedencia" class="mb-2 block text-sm font-semibold text-slate-700">Procedencia</label>
                <input id="procedencia" name="procedencia" value="{{ old('procedencia', $invitado?->procedencia) }}" maxlength="150" placeholder="Institución o procedencia" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
            </div>
            <div>
                <label for="estado" class="mb-2 block text-sm font-semibold text-slate-700">Estado</label>
                <select id="estado" name="estado" required class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                    <option value="activo" @selected(old('estado', $invitado?->estado ?? 'activo') === 'activo')>Activo</option>
                    <option value="inactivo" @selected(old('estado', $invitado?->estado) === 'inactivo')>Inactivo</option>
                </select>
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('invitados.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-[#272F7A] transition hover:bg-indigo-50">Cancelar</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-[#1E2A5A] to-[#272F7A] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:from-[#272F7A] hover:to-[#4959A6]">{{ $esEdicion ? 'Guardar cambios' : 'Crear invitado' }}</button>
    </div>
</form>

<div>
    <label for="equipo_id" class="mb-2 block text-sm font-semibold text-slate-700">Equipo asociado</label>
    <select id="equipo_id" name="equipo_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Selecciona un equipo</option>
        @foreach ($equipos as $equipo)
            <option value="{{ $equipo->id }}" @selected((string) old('equipo_id', $accesorio->equipo_id ?? request('equipo_id')) === (string) $equipo->id)>{{ $equipo->codigo }} · {{ $equipo->marca }} {{ $equipo->modelo }}</option>
        @endforeach
    </select>
    @error('equipo_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="nombre" class="mb-2 block text-sm font-semibold text-slate-700">Nombre del accesorio</label>
        <input id="nombre" name="nombre" value="{{ old('nombre', $accesorio->nombre) }}" maxlength="50" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('nombre')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="marca" class="mb-2 block text-sm font-semibold text-slate-700">Marca (opcional)</label>
        <input id="marca" name="marca" value="{{ old('marca', $accesorio->marca) }}" maxlength="50" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('marca')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="modelo" class="mb-2 block text-sm font-semibold text-slate-700">Modelo (opcional)</label>
        <input id="modelo" name="modelo" value="{{ old('modelo', $accesorio->modelo) }}" maxlength="100" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('modelo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="numero_serie" class="mb-2 block text-sm font-semibold text-slate-700">Número de serie (opcional)</label>
        <input id="numero_serie" name="numero_serie" value="{{ old('numero_serie', $accesorio->numero_serie) }}" maxlength="100" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('numero_serie')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="cantidad" class="mb-2 block text-sm font-semibold text-slate-700">Cantidad</label>
        <input id="cantidad" name="cantidad" type="number" min="1" value="{{ old('cantidad', $accesorio->cantidad) }}" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('cantidad')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="estado" class="mb-2 block text-sm font-semibold text-slate-700">Estado</label>
        <select id="estado" name="estado" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach (['Disponible', 'En uso', 'Dañado', 'Mantenimiento'] as $estado)
                <option value="{{ $estado }}" @selected(old('estado', $accesorio->estado) === $estado)>{{ $estado }}</option>
            @endforeach
        </select>
        @error('estado')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
<div>
    <label for="imagen_referencial" class="mb-2 block text-sm font-semibold text-slate-700">Foto referencial (opcional)</label>
    @if ($accesorio->imagen_referencial)
        <img src="{{ asset('storage/'.$accesorio->imagen_referencial) }}" alt="Foto actual de {{ $accesorio->nombre }}" class="mb-3 h-40 w-full rounded-xl border border-slate-200 object-cover sm:w-64">
    @endif
    <input id="imagen_referencial" name="imagen_referencial" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-slate-300 bg-white text-sm file:mr-4 file:border-0 file:bg-indigo-50 file:px-4 file:py-3 file:font-semibold file:text-[#272F7A]">
    <p class="mt-1 text-xs text-slate-500">JPG, PNG o WebP; máximo 2 MB.</p>
    @error('imagen_referencial')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="observaciones" class="mb-2 block text-sm font-semibold text-slate-700">Observaciones</label>
    <textarea id="observaciones" name="observaciones" rows="4" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('observaciones', $accesorio->observaciones) }}</textarea>
    @error('observaciones')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

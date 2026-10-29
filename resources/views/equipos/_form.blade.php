<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="codigo" class="mb-2 block text-sm font-semibold text-slate-700">Código institucional</label>
        <input id="codigo" name="codigo" value="{{ old('codigo', $equipo->codigo ?? '') }}" maxlength="20" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('codigo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="tipo_equipo" class="mb-2 block text-sm font-semibold text-slate-700">Tipo de equipo</label>
        <select id="tipo_equipo" name="tipo_equipo" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Seleccione un tipo</option>
            @foreach (['PC', 'Laptop', 'Tablet'] as $tipoEquipo)
                <option value="{{ $tipoEquipo }}" @selected(old('tipo_equipo', $equipo->tipo_equipo ?? '') === $tipoEquipo)>{{ $tipoEquipo }}</option>
            @endforeach
        </select>
        @error('tipo_equipo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="marca" class="mb-2 block text-sm font-semibold text-slate-700">Marca</label>
        <input id="marca" name="marca" value="{{ old('marca', $equipo->marca ?? '') }}" maxlength="50" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('marca')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="modelo" class="mb-2 block text-sm font-semibold text-slate-700">Modelo</label>
        <input id="modelo" name="modelo" value="{{ old('modelo', $equipo->modelo ?? '') }}" maxlength="100" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('modelo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="numero_serie" class="mb-2 block text-sm font-semibold text-slate-700">Número de serie</label>
        <input id="numero_serie" name="numero_serie" value="{{ old('numero_serie', $equipo->numero_serie ?? '') }}" maxlength="100" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('numero_serie')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="modo_uso" class="mb-2 block text-sm font-semibold text-slate-700">Modo de uso</label>
        <select id="modo_uso" name="modo_uso" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Seleccione un modo</option>
            @foreach (['individual' => 'Individual', 'grupal' => 'Grupal'] as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('modo_uso', $equipo->modo_uso ?? '') === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
        @error('modo_uso')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="estado" class="mb-2 block text-sm font-semibold text-slate-700">Estado</label>
        <select id="estado" name="estado" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Seleccione un estado</option>
            @foreach (['Disponible', 'En uso', 'Mantenimiento', 'Fuera de servicio'] as $estado)
                <option value="{{ $estado }}" @selected(old('estado', $equipo->estado ?? '') === $estado)>{{ $estado }}</option>
            @endforeach
        </select>
        @error('estado')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="mac" class="mb-2 block text-sm font-semibold text-slate-700">Dirección MAC</label>
        <input id="mac" name="mac" value="{{ old('mac', $equipo->mac ?? '') }}" maxlength="17" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('mac')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
<div>
    <label for="imagen_referencial" class="mb-2 block text-sm font-semibold text-slate-700">{{ isset($equipo) ? 'Foto referencial' : 'Foto referencial (opcional)' }}</label>
    @if (!empty($equipo?->imagen_referencial))
        <img src="{{ asset('storage/'.$equipo->imagen_referencial) }}" alt="Foto actual del equipo {{ $equipo->codigo }}" class="mb-3 h-40 w-full rounded-xl border border-slate-200 object-cover sm:w-64">
    @endif
    <input id="imagen_referencial" name="imagen_referencial" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-xl border border-slate-300 bg-white text-sm file:mr-4 file:border-0 file:bg-indigo-50 file:px-4 file:py-3 file:font-semibold file:text-[#272F7A]">
    <p class="mt-1 text-xs text-slate-500">JPG, PNG o WebP; máximo 2 MB.</p>
    @error('imagen_referencial')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

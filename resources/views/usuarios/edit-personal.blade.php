<x-admin-layout>
    <x-admin-shell>
    <div class="min-h-screen bg-[#F4F7FC] text-slate-800">
        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <header class="relative flex flex-col gap-5 overflow-hidden rounded-3xl bg-gradient-to-br from-[#0B1226] via-[#1E2A5A] to-[#272F7A] px-6 py-7 text-white shadow-2xl shadow-indigo-950/20 [&_p:first-child]:!text-cyan-200 sm:flex-row sm:items-center sm:justify-between sm:px-8 sm:py-8">
                <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl" aria-hidden="true"></div>
                <div class="dashboard-grid pointer-events-none absolute inset-0 opacity-30" aria-hidden="true"></div>
                <div>
                    <p class="text-sm font-medium text-[#4959A6]">Administración / Usuarios / Datos personales</p>
                    <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">Editar datos personales</h1>
                    <p class="mt-2 text-sm leading-6 text-indigo-100/80">
                        Editas el perfil de {{ $perfil->nombres }} {{ $perfil->apellido_paterno }}.
                        Las credenciales y el tipo de cuenta no se modifican aquí.
                    </p>
                </div>
                <a href="{{ route('usuarios.show', $usuario->id) }}"
                   class="relative inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-bold text-[#272F7A] shadow-sm transition hover:bg-indigo-50 hover:text-[#4959A6]">
                    Volver a la ficha
                </a>
            </header>

            @if ($errors->any())
                <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-semibold">Revisa los datos ingresados:</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $fotoActual = $perfil->foto_personal;
                $fotoActualDisponible = $fotoActual
                    && \Illuminate\Support\Facades\Storage::disk('public')->exists($fotoActual);
            @endphp

            <form
                method="POST"
                action="{{ route('usuarios.personal.update', $usuario->id) }}"
                enctype="multipart/form-data"
                class="space-y-6"
                x-data="{
                    photoPreview: null,
                    photoName: '',
                    previewPhoto(event) {
                        const file = event.target.files[0];
                        if (this.photoPreview) URL.revokeObjectURL(this.photoPreview);
                        this.photoPreview = file ? URL.createObjectURL(file) : null;
                        this.photoName = file ? file.name : '';
                    }
                }"
            >
                @csrf
                @method('PATCH')

                <section class="dashboard-rise rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-800">
                            {{ $usuario->tipo_usuario === 'estudiante' ? 'Información estudiantil' : 'Información docente' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            El estado de acceso se gestiona por separado desde la ficha del usuario.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="nombres" class="mb-2 block text-sm font-semibold text-slate-700">Nombres</label>
                            <input id="nombres" name="nombres" value="{{ old('nombres', $perfil->nombres) }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('nombres') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="apellido_paterno" class="mb-2 block text-sm font-semibold text-slate-700">Apellido paterno</label>
                            <input id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $perfil->apellido_paterno) }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('apellido_paterno') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="apellido_materno" class="mb-2 block text-sm font-semibold text-slate-700">Apellido materno</label>
                            <input id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno', $perfil->apellido_materno) }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('apellido_materno') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ci" class="mb-2 block text-sm font-semibold text-slate-700">Carnet de identidad</label>
                            <input id="ci" name="ci" value="{{ old('ci', $perfil->ci) }}" required maxlength="20" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('ci') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ci_extension" class="mb-2 block text-sm font-semibold text-slate-700">Extensión del CI</label>
                            <select id="ci_extension" name="ci_extension" required class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                <option value="">Selecciona un departamento</option>
                                @foreach (['LP' => 'La Paz', 'OR' => 'Oruro', 'PT' => 'Potosí', 'CB' => 'Cochabamba', 'SC' => 'Santa Cruz', 'TJ' => 'Tarija', 'CH' => 'Chuquisaca', 'BE' => 'Beni', 'PD' => 'Pando'] as $extension => $departamento)
                                    <option value="{{ $extension }}" @selected(old('ci_extension', $perfil->ci_extension) === $extension)>{{ $departamento }} ({{ $extension }})</option>
                                @endforeach
                            </select>
                            @error('ci_extension') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="telefono" class="mb-2 block text-sm font-semibold text-slate-700">Teléfono</label>
                            <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $perfil->telefono) }}" maxlength="20" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('telefono') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        @if (false)
                        <div>
                            <label for="foto_personal" class="mb-2 block text-sm font-semibold text-slate-700">Fotografía personal</label>
                            <input id="foto_personal" name="foto_personal" type="file" accept="image/jpeg,image/png,image/webp" x-on:change="previewPhoto($event)" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                            <div class="mt-3 flex h-32 w-32 items-center justify-center overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50">
                                @if ($perfil->foto_personal && \Illuminate\Support\Facades\Storage::disk('public')->exists($perfil->foto_personal))
                                    <img x-show="!photoPreview" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($perfil->foto_personal) }}" alt="Fotografía actual del perfil" class="h-full w-full object-cover">
                                @else
                                    <span x-show="!photoPreview" class="px-3 text-center text-xs text-slate-500">Sin fotografía</span>
                                @endif
                                <img x-show="photoPreview" :src="photoPreview" alt="Vista previa de la nueva fotografía" class="h-full w-full object-cover">
                            </div>
                            @error('foto_personal') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        </div>
                        @endif

                        @if ($usuario->tipo_usuario === 'estudiante')
                            <div>
                                <label for="matricula" class="mb-2 block text-sm font-semibold text-slate-700">Matrícula (opcional)</label>
                                <input id="matricula" name="matricula" value="{{ old('matricula', $perfil->matricula) }}" maxlength="30" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('matricula') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="semestre" class="mb-2 block text-sm font-semibold text-slate-700">Semestre</label>
                                <select id="semestre" name="semestre" required class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                    @foreach ([1 => 'Primero', 2 => 'Segundo', 3 => 'Tercero', 4 => 'Cuarto'] as $numeroSemestre => $nombreSemestre)
                                        <option value="{{ $numeroSemestre }}" @selected((int) old('semestre', $perfil->semestre) === $numeroSemestre)>{{ $nombreSemestre }}</option>
                                    @endforeach
                                </select>
                                @error('semestre') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="generacion_ingreso" class="mb-2 block text-sm font-semibold text-slate-700">Generación de ingreso</label>
                                <input id="generacion_ingreso" name="generacion_ingreso" value="{{ old('generacion_ingreso', $perfil->generacion_ingreso) }}" placeholder="I-2026" pattern="(?:I|II)-[0-9]{4}" maxlength="7" required class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                <p class="mt-1 text-xs text-slate-500">Formato: I-2026 o II-2026.</p>
                                @error('generacion_ingreso') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="correo_institucional" class="mb-2 block text-sm font-semibold text-slate-700">Correo institucional</label>
                                <input id="correo_institucional" name="correo_institucional" type="email" value="{{ old('correo_institucional', $perfil->correo_institucional) }}" maxlength="150" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('correo_institucional') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="correo_personal" class="mb-2 block text-sm font-semibold text-slate-700">Correo personal</label>
                                <input id="correo_personal" name="correo_personal" type="email" value="{{ old('correo_personal', $perfil->correo_personal) }}" maxlength="150" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('correo_personal') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="direccion" class="mb-2 block text-sm font-semibold text-slate-700">Dirección</label>
                                <input id="direccion" name="direccion" value="{{ old('direccion', $perfil->direccion) }}" maxlength="255" autocomplete="street-address" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('direccion') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @else
                            <div>
                                <label for="correo_personal" class="mb-2 block text-sm font-semibold text-slate-700">Correo personal</label>
                                <input id="correo_personal" name="correo_personal" type="email" value="{{ old('correo_personal', $perfil->correo_personal) }}" maxlength="150" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('correo_personal') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="cargo" class="mb-2 block text-sm font-semibold text-slate-700">Cargo</label>
                                <input id="cargo" name="cargo" value="{{ old('cargo', $perfil->cargo) }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('cargo') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="nivel_academico" class="mb-2 block text-sm font-semibold text-slate-700">Nivel académico</label>
                                <input id="nivel_academico" name="nivel_academico" value="{{ old('nivel_academico', $perfil->nivel_academico) }}" maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('nivel_academico') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="especialidad" class="mb-2 block text-sm font-semibold text-slate-700">Especialidad</label>
                                <input id="especialidad" name="especialidad" value="{{ old('especialidad', $perfil->especialidad) }}" maxlength="150" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('especialidad') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="fecha_ingreso" class="mb-2 block text-sm font-semibold text-slate-700">Fecha de ingreso</label>
                                <input id="fecha_ingreso" name="fecha_ingreso" type="date" value="{{ old('fecha_ingreso', $perfil->fecha_ingreso?->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('fecha_ingreso') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                </section>

                <section class="dashboard-rise rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
                    <div class="mb-5">
                        <h2 class="text-lg font-bold text-slate-800">Fotografía personal</h2>
                        <p class="mt-1 text-sm text-slate-500">Actualiza la imagen que se mostrará en el perfil y en el panel.</p>
                    </div>

                    @include('profile.partials.photo-upload')
                </section>

                <section class="rounded-2xl border border-slate-200 bg-slate-100 p-5 sm:p-6">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">Cuenta protegida</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Usuario: <span class="font-semibold text-slate-800">{{ $usuario->username }}</span>
                        · Rol: <span class="font-semibold text-slate-800">{{ ucfirst($usuario->tipo_usuario) }}</span>
                        · Las credenciales no se pueden modificar desde esta pantalla.
                    </p>
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('usuarios.show', $usuario->id) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#4959A6]">
                        Guardar datos personales
                    </button>
                </div>
            </form>
        </main>
    </div>
    </x-admin-shell>
</x-admin-layout>

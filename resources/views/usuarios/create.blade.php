<x-admin-layout>
    <x-admin-shell>
    <div class="min-h-screen bg-[#F4F7FC] text-slate-800">
        <main class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <header class="relative flex flex-col gap-5 overflow-hidden rounded-3xl bg-gradient-to-br from-[#0B1226] via-[#1E2A5A] to-[#272F7A] px-6 py-7 text-white shadow-2xl shadow-indigo-950/20 [&_p:first-child]:!text-cyan-200 sm:flex-row sm:items-center sm:justify-between sm:px-8 sm:py-8">
                <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl" aria-hidden="true"></div>
                <div class="dashboard-grid pointer-events-none absolute inset-0 opacity-30" aria-hidden="true"></div>
                <div>
                    <p class="text-sm font-medium text-[#4959A6]">Administración / Usuarios</p>
                    <h1 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">Crear perfil y cuenta</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-indigo-100/80">
                        Registra los datos personales y crea su acceso en una sola operación.
                        El nombre de la cuenta se formará con los nombres y apellidos.
                    </p>
                </div>
                <a href="{{ route('usuarios.index') }}"
                   class="relative inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-white px-5 py-3 text-sm font-bold text-[#272F7A] shadow-sm transition hover:bg-indigo-50 hover:text-[#4959A6]">
                    Volver al listado
                </a>
            </header>

            @if ($errors->any())
                <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-semibold">No se pudo crear el perfil y la cuenta. Revisa estos campos:</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('usuarios.store') }}"
                enctype="multipart/form-data"
                autocomplete="off"
                class="space-y-6"
                x-data="{
                    tipoUsuario: @js(old('tipo_usuario', 'docente')),
                    photoPreview: null,
                    photoName: '',
                    suggestCredentials() {
                        const username = [
                            document.getElementById('nombres').value,
                            document.getElementById('apellido_paterno').value,
                        ].filter(Boolean).join('_')
                            .normalize('NFD')
                            .replace(/[\u0300-\u036f]/g, '')
                            .toLowerCase()
                            .replace(/[^a-z0-9_]+/g, '')
                            .slice(0, 50);
                        const surname = document.getElementById('apellido_paterno').value
                            .normalize('NFD')
                            .replace(/[\u0300-\u036f]/g, '')
                            .toLowerCase()
                            .replace(/[^a-z0-9]+/g, '');
                        const ciInput = document.getElementById('ci');
                        const ci = (ciInput?.value || '').replace(/\D/g, '');
                        const password = `${surname}${ci}`;

                        document.getElementById('username').value = username;
                        document.getElementById('password').value = password;
                        document.getElementById('password_confirmation').value = password;
                    },
                    previewPhoto(event) {
                        const file = event.target.files[0];
                        if (this.photoPreview) URL.revokeObjectURL(this.photoPreview);
                        this.photoPreview = file ? URL.createObjectURL(file) : null;
                        this.photoName = file ? file.name : '';
                    }
                }"
            >
                @csrf

                <section class="dashboard-rise rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-slate-800">Datos personales</h2>
                        <p class="mt-1 text-sm text-slate-500">Información del registro docente o estudiantil.</p>
                    </div>

                    <div class="mb-5">
                        <label for="tipo_usuario" class="mb-2 block text-sm font-semibold text-slate-700">
                            Tipo de perfil
                        </label>
                        <select
                            id="tipo_usuario"
                            name="tipo_usuario"
                            x-model="tipoUsuario"
                            required
                            class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]"
                        >
                            <option value="docente">Docente</option>
                            <option value="administrador">Administrador</option>
                            <option value="estudiante">Estudiante</option>
                        </select>
                        @error('tipo_usuario') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="nombres" class="mb-2 block text-sm font-semibold text-slate-700">Nombres</label>
                        <input id="nombres" name="perfil[nombres]" value="{{ old('perfil.nombres') }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('perfil.nombres') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="apellido_paterno" class="mb-2 block text-sm font-semibold text-slate-700">Apellido paterno</label>
                        <input id="apellido_paterno" name="perfil[apellido_paterno]" value="{{ old('perfil.apellido_paterno') }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('perfil.apellido_paterno') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="apellido_materno" class="mb-2 block text-sm font-semibold text-slate-700">Apellido materno</label>
                            <input id="apellido_materno" name="perfil[apellido_materno]" value="{{ old('perfil.apellido_materno') }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('perfil.apellido_materno') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ci" class="mb-2 block text-sm font-semibold text-slate-700">Carnet de identidad</label>
                            <input id="ci" name="perfil[ci]" value="{{ old('perfil.ci') }}" required maxlength="20" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('perfil.ci') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ci_extension" class="mb-2 block text-sm font-semibold text-slate-700">Extensión del CI</label>
                            <select id="ci_extension" name="perfil[ci_extension]" required class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                <option value="">Selecciona una extensión</option>
                                @foreach (['LP' => 'La Paz', 'OR' => 'Oruro', 'PT' => 'Potosí', 'CB' => 'Cochabamba', 'SC' => 'Santa Cruz', 'TJ' => 'Tarija', 'CH' => 'Chuquisaca', 'BE' => 'Beni', 'PD' => 'Pando'] as $extension => $departamento)
                                    <option value="{{ $extension }}" @selected(old('perfil.ci_extension') === $extension)>{{ $departamento }} ({{ $extension }})</option>
                                @endforeach
                            </select>
                            @error('perfil.ci_extension') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="telefono" class="mb-2 block text-sm font-semibold text-slate-700">Teléfono</label>
                            <input id="telefono" name="perfil[telefono]" type="tel" value="{{ old('perfil.telefono') }}" maxlength="20" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('perfil.telefono') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="correo_personal" class="mb-2 block text-sm font-semibold text-slate-700">Correo personal</label>
                            <input id="correo_personal" name="perfil[correo_personal]" type="email" value="{{ old('perfil.correo_personal') }}" maxlength="150" placeholder="nombre@gmail.com" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('perfil.correo_personal') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <fieldset x-show="tipoUsuario !== 'estudiante'" :disabled="tipoUsuario === 'estudiante'" class="mt-6 rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 sm:p-5">
                        <legend class="px-2 text-sm font-bold text-[#272F7A]">Información docente</legend>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="cargo" class="mb-2 block text-sm font-semibold text-slate-700">Cargo</label>
                                <input id="cargo" name="perfil[cargo]" value="{{ old('perfil.cargo') }}" :required="tipoUsuario === 'docente'" maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('perfil.cargo') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="nivel_academico" class="mb-2 block text-sm font-semibold text-slate-700">Nivel académico</label>
                                <input id="nivel_academico" name="perfil[nivel_academico]" value="{{ old('perfil.nivel_academico') }}" maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            </div>
                            <div>
                                <label for="especialidad" class="mb-2 block text-sm font-semibold text-slate-700">Especialidad</label>
                                <input id="especialidad" name="perfil[especialidad]" value="{{ old('perfil.especialidad') }}" maxlength="150" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('perfil.especialidad') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="fecha_ingreso" class="mb-2 block text-sm font-semibold text-slate-700">Fecha de ingreso</label>
                                <input id="fecha_ingreso" name="perfil[fecha_ingreso]" type="date" value="{{ old('perfil.fecha_ingreso') }}" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset x-show="tipoUsuario === 'estudiante'" :disabled="tipoUsuario !== 'estudiante'" class="mt-6 rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 sm:p-5">
                        <legend class="px-2 text-sm font-bold text-[#272F7A]">Información estudiantil</legend>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="matricula" class="mb-2 block text-sm font-semibold text-slate-700">Matrícula</label>
                                <input id="matricula" name="perfil[matricula]" value="{{ old('perfil.matricula') }}" maxlength="30" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('perfil.matricula') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="semestre" class="mb-2 block text-sm font-semibold text-slate-700">Semestre</label>
                                <select id="semestre" name="perfil[semestre]" :required="tipoUsuario === 'estudiante'" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                    <option value="">Selecciona un semestre</option>
                                    @foreach ([1 => 'Primero', 2 => 'Segundo', 3 => 'Tercero', 4 => 'Cuarto'] as $numeroSemestre => $nombreSemestre)
                                        <option value="{{ $numeroSemestre }}" @selected((int) old('perfil.semestre') === $numeroSemestre)>{{ $nombreSemestre }}</option>
                                    @endforeach
                                </select>
                                @error('perfil.semestre') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="generacion_ingreso" class="mb-2 block text-sm font-semibold text-slate-700">Generación de ingreso</label>
                                <input id="generacion_ingreso" name="perfil[generacion_ingreso]" value="{{ old('perfil.generacion_ingreso') }}" placeholder="I-2026" pattern="(?:I|II)-[0-9]{4}" maxlength="7" :required="tipoUsuario === 'estudiante'" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                <p class="mt-1 text-xs text-slate-500">Formato: I-2026 o II-2026.</p>
                                @error('perfil.generacion_ingreso') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="correo_institucional" class="mb-2 block text-sm font-semibold text-slate-700">Correo institucional</label>
                                <input id="correo_institucional" name="perfil[correo_institucional]" type="email" value="{{ old('perfil.correo_institucional') }}" maxlength="150" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('perfil.correo_institucional') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="direccion" class="mb-2 block text-sm font-semibold text-slate-700">Dirección</label>
                                <input id="direccion" name="perfil[direccion]" value="{{ old('perfil.direccion') }}" maxlength="255" autocomplete="street-address" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('perfil.direccion') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </fieldset>
                </section>

                @php($fotoActual = null)
                @php($fotoActualDisponible = false)
                <section class="dashboard-rise rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
                    <div class="mb-5">
                        <h2 class="text-lg font-bold text-slate-800">Fotografía personal</h2>
                        <p class="mt-1 text-sm text-slate-500">Añade la imagen que se mostrará en el perfil y en el panel.</p>
                    </div>

                    @include('profile.partials.photo-upload')
                </section>

                <section class="dashboard-rise rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Acceso al sistema</h2>
                            <p class="mt-1 text-sm text-slate-500">Las credenciales se configuran al crear la cuenta.</p>
                        </div>
                        <button
                            type="button"
                            x-on:click="suggestCredentials()"
                            class="inline-flex w-full shrink-0 items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-bold text-[#272F7A] transition hover:border-indigo-300 hover:bg-indigo-100 sm:w-auto"
                        >
                            Sugerir credenciales
                        </button>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="username" class="mb-2 block text-sm font-semibold text-slate-700">Nombre de usuario</label>
                            <input id="username" name="username" value="" required maxlength="50" autocomplete="off" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                            @error('username') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Contraseña inicial</label>
                            <div class="relative" x-data="{ passwordVisible: false }">
                                <input id="password" name="password" type="password" x-bind:type="passwordVisible ? 'text' : 'password'" x-on:input="document.getElementById('password_confirmation').value = $event.target.value" required minlength="8" autocomplete="new-password" class="w-full rounded-xl border-slate-300 px-4 py-3 pr-20 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                <button
                                    type="button"
                                    x-on:click="passwordVisible = !passwordVisible"
                                    class="absolute inset-y-0 right-0 px-3 text-xs font-bold text-[#272F7A] hover:text-[#4959A6]"
                                    x-text="passwordVisible ? 'Ocultar' : 'Ver'"
                                ></button>
                            </div>
                            @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <input id="password_confirmation" name="password_confirmation" type="hidden" value="{{ old('password_confirmation') }}">
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('usuarios.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#272F7A] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#4959A6]">
                        Crear perfil y cuenta
                    </button>
                </div>
            </form>
        </main>
    </div>
    </x-admin-shell>
</x-admin-layout>

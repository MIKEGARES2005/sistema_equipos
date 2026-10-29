<section>
    <header class="mb-6 border-b border-slate-100 pb-5">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                <x-icon name="user" class="h-5 w-5" />
            </span>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-500">Configuración personal</p>
                <h2 class="mt-1 text-lg font-black tracking-tight text-slate-900">Información de mi perfil</h2>
            </div>
        </div>
        <p class="mt-3 text-sm leading-6 text-slate-500">
            Administra los datos de tu cuenta y tu información personal. Estos cambios solo afectan tu propio perfil.
        </p>
    </header>

    @php
        $perfilPersonal = $user->docente ?? $user->estudiante;
        $fotoActual = $perfilPersonal?->foto_personal;
        $fotoActualDisponible = $fotoActual
            && \Illuminate\Support\Facades\Storage::disk('public')->exists($fotoActual);
    @endphp

    @if ($perfilPersonal === null)
        <div class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm leading-6 text-slate-600">
            Esta cuenta administrativa no tiene un perfil personal asociado. Puedes cambiar tu nombre de usuario aquí y tu contraseña desde el panel de seguridad.
        </div>
    @endif

    <form
        method="post"
        action="{{ request()->routeIs('estudiante.*') ? route('estudiante.profile.update') : route('profile.update') }}"
        class="space-y-6"
        enctype="multipart/form-data"
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
        @method('patch')

            <section class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 sm:p-5">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200" aria-hidden="true">
                        <x-icon name="user" class="h-4 w-4" />
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-800">Datos de acceso</h3>
                        <p class="text-xs text-slate-500">Puedes cambiar tu nombre de usuario. El rol lo administra el sistema.</p>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="username" value="Nombre de usuario" />
                        <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)" required maxlength="50" autocomplete="username" />
                        <p class="mt-1.5 text-xs text-slate-500">Solo letras, números, guiones y guiones bajos.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('username')" />
                    </div>
                    <div>
                        <x-input-label for="rol_display" value="Rol de la cuenta" />
                        <x-text-input id="rol_display" type="text" class="mt-1 block w-full bg-slate-100 text-slate-500" :value="ucfirst($user->tipo_usuario)" disabled />
                        <p class="mt-1.5 text-xs text-slate-500">El rol no se puede modificar.</p>
                    </div>
                </div>
            </section>

            @if ($user->docente)
                <section class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200" aria-hidden="true">
                            <x-icon name="clipboard" class="h-4 w-4" />
                        </span>
                        <div>
                            <h3 class="font-bold text-slate-800">Datos personales</h3>
                            <p class="text-xs text-slate-500">Información registrada en el plantel docente.</p>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="nombres" value="Nombres" />
                            <x-text-input id="nombres" name="nombres" type="text" class="mt-1 block w-full" :value="old('nombres', $user->docente->nombres)" required autocomplete="given-name" />
                            <x-input-error class="mt-2" :messages="$errors->get('nombres')" />
                        </div>
                        <div>
                            <x-input-label for="apellido_paterno" value="Apellido paterno" />
                            <x-text-input id="apellido_paterno" name="apellido_paterno" type="text" class="mt-1 block w-full" :value="old('apellido_paterno', $user->docente->apellido_paterno)" required autocomplete="family-name" />
                            <x-input-error class="mt-2" :messages="$errors->get('apellido_paterno')" />
                        </div>
                        <div>
                            <x-input-label for="apellido_materno" value="Apellido materno" />
                            <x-text-input id="apellido_materno" name="apellido_materno" type="text" class="mt-1 block w-full" :value="old('apellido_materno', $user->docente->apellido_materno)" autocomplete="additional-name" />
                            <x-input-error class="mt-2" :messages="$errors->get('apellido_materno')" />
                        </div>
                        <div>
                            <x-input-label for="ci" value="Carnet de identidad" />
                            <x-text-input id="ci" name="ci" type="text" class="mt-1 block w-full" :value="old('ci', $user->docente->ci)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('ci')" />
                        </div>
                        <div>
                            <x-input-label for="ci_extension" value="Extensión del CI" />
                            <select id="ci_extension" name="ci_extension" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Sin extensión</option>
                                @foreach ([
                                    'LP' => 'La Paz',
                                    'OR' => 'Oruro',
                                    'PT' => 'Potosí',
                                    'CB' => 'Cochabamba',
                                    'SC' => 'Santa Cruz',
                                    'TJ' => 'Tarija',
                                    'CH' => 'Chuquisaca',
                                    'BE' => 'Beni',
                                    'PD' => 'Pando',
                                ] as $codigo => $departamento)
                                    <option value="{{ $codigo }}" @selected(old('ci_extension', $user->docente->ci_extension) === $codigo)>
                                        {{ $codigo }} — {{ $departamento }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('ci_extension')" />
                        </div>
                        <div>
                            <x-input-label for="telefono" value="Teléfono" />
                            <x-text-input id="telefono" name="telefono" type="tel" class="mt-1 block w-full" :value="old('telefono', $user->docente->telefono)" autocomplete="tel" />
                            <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
                        </div>
                        <div>
                            <x-input-label for="correo_personal" value="Correo personal" />
                            <x-text-input id="correo_personal" name="correo_personal" type="email" class="mt-1 block w-full" :value="old('correo_personal', $user->docente->correo_personal)" autocomplete="email" />
                            <x-input-error class="mt-2" :messages="$errors->get('correo_personal')" />
                        </div>
                        <div>
                            <x-input-label for="cargo" value="Cargo" />
                            <x-text-input id="cargo" name="cargo" type="text" class="mt-1 block w-full" :value="old('cargo', $user->docente->cargo)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('cargo')" />
                        </div>
                        <div>
                            <x-input-label for="especialidad" value="Especialidad" />
                            <x-text-input id="especialidad" name="especialidad" type="text" class="mt-1 block w-full" :value="old('especialidad', $user->docente->especialidad)" />
                            <x-input-error class="mt-2" :messages="$errors->get('especialidad')" />
                        </div>
                        <div>
                            <x-input-label for="nivel_academico" value="Nivel académico" />
                            <x-text-input id="nivel_academico" name="nivel_academico" type="text" class="mt-1 block w-full" :value="old('nivel_academico', $user->docente->nivel_academico)" />
                            <x-input-error class="mt-2" :messages="$errors->get('nivel_academico')" />
                        </div>
                        <div>
                            <x-input-label for="fecha_ingreso" value="Fecha de ingreso" />
                            <x-text-input id="fecha_ingreso" name="fecha_ingreso" type="date" class="mt-1 block w-full" :value="old('fecha_ingreso', $user->docente->fecha_ingreso?->format('Y-m-d'))" />
                            <x-input-error class="mt-2" :messages="$errors->get('fecha_ingreso')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="foto_personal" value="Fotografía personal" />
                            <div class="mt-2">
                                @include('profile.partials.photo-upload')
                            </div>
                        </div>
                    </div>
                </section>
            @elseif ($user->estudiante)
                <section class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200" aria-hidden="true">
                            <x-icon name="academic-cap" class="h-4 w-4" />
                        </span>
                        <div>
                            <h3 class="font-bold text-slate-800">Datos académicos</h3>
                            <p class="text-xs text-slate-500">Esta información la administra el plantel; si necesitas corregirla, contacta a administración.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <x-input-label for="nombres_display" value="Nombres" />
                            <x-text-input id="nombres_display" type="text" class="mt-1 block w-full bg-slate-100 text-slate-500" :value="$user->estudiante->nombres" disabled />
                        </div>
                        <div>
                            <x-input-label for="apellidos_display" value="Apellidos" />
                            <x-text-input id="apellidos_display" type="text" class="mt-1 block w-full bg-slate-100 text-slate-500" :value="trim($user->estudiante->apellido_paterno.' '.$user->estudiante->apellido_materno)" disabled />
                        </div>
                        <div>
                            <x-input-label for="matricula_display" value="Matrícula" />
                            <x-text-input id="matricula_display" type="text" class="mt-1 block w-full bg-slate-100 text-slate-500" :value="$user->estudiante->matricula" disabled />
                        </div>
                        <div>
                            <x-input-label for="semestre_display" value="Semestre" />
                            <x-text-input id="semestre_display" type="text" class="mt-1 block w-full bg-slate-100 text-slate-500" :value="$user->estudiante->semestre.'° semestre'" disabled />
                        </div>
                        <div class="col-span-2">
                            <x-input-label for="ci_display" value="Carnet de identidad" />
                            <x-text-input id="ci_display" type="text" class="mt-1 block w-full bg-slate-100 text-slate-500" :value="trim($user->estudiante->ci.' '.$user->estudiante->ci_extension)" disabled />
                        </div>
                        <div class="col-span-2">
                            <x-input-label for="correo_institucional_display" value="Correo institucional" />
                            <x-text-input id="correo_institucional_display" type="text" class="mt-1 block w-full bg-slate-100 text-slate-500" :value="$user->estudiante->correo_institucional ?: 'No registrado'" disabled />
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200" aria-hidden="true">
                            <x-icon name="mail" class="h-4 w-4" />
                        </span>
                        <div>
                            <h3 class="font-bold text-slate-800">Datos de contacto</h3>
                            <p class="text-xs text-slate-500">Puedes actualizar tus datos de contacto y tu fotografía.</p>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="telefono" value="Teléfono" />
                            <x-text-input id="telefono" name="telefono" type="tel" class="mt-1 block w-full" :value="old('telefono', $user->estudiante->telefono)" autocomplete="tel" />
                            <x-input-error class="mt-2" :messages="$errors->get('telefono')" />
                        </div>
                        <div>
                            <x-input-label for="correo_personal" value="Correo personal" />
                            <x-text-input id="correo_personal" name="correo_personal" type="email" class="mt-1 block w-full" :value="old('correo_personal', $user->estudiante->correo_personal)" autocomplete="email" />
                            <x-input-error class="mt-2" :messages="$errors->get('correo_personal')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="direccion" value="Dirección" />
                            <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full" :value="old('direccion', $user->estudiante->direccion)" autocomplete="street-address" />
                            <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="foto_personal" value="Fotografía personal" />
                            <div class="mt-2">
                                @include('profile.partials.photo-upload')
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @include('profile.partials.update-password-form')

            <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:-translate-y-0.5 {{ $esEstudiante ? 'bg-gradient-to-r from-emerald-600 to-teal-700 shadow-emerald-900/10 hover:from-emerald-700 hover:to-teal-800' : 'bg-gradient-to-r from-[#1E2A5A] to-[#272F7A] shadow-indigo-950/10 hover:from-[#272F7A] hover:to-[#4959A6]' }}">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ $perfilPersonal === null ? 'Guardar nombre de usuario' : 'Guardar cambios' }}
                </button>
            </div>
    </form>
</section>

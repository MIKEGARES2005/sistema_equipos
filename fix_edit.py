content = open('resources/views/usuarios/edit-personal.blade.php', encoding='utf-8').read()

old = """                            <div>
                                <label for="cargo" class="mb-2 block text-sm font-semibold text-slate-700">Cargo</label>
                                <input id="cargo" name="cargo" value="{{ old('cargo', $perfil->cargo) }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('cargo') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="nivel_academico" class="mb-2 block text-sm font-semibold text-slate-700">Nivel académico</label>"""

new = """                            <div>
                                <label for="cargo" class="mb-2 block text-sm font-semibold text-slate-700">Cargo</label>
                                <input id="cargo" name="cargo" value="{{ old('cargo', $perfil->cargo) }}" required maxlength="100" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('cargo') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="especialidad" class="mb-2 block text-sm font-semibold text-slate-700">Especialidad</label>
                                <input id="especialidad" name="especialidad" value="{{ old('especialidad', $perfil->especialidad) }}" maxlength="150" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('especialidad') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="correo_personal" class="mb-2 block text-sm font-semibold text-slate-700">Correo personal</label>
                                <input id="correo_personal" name="correo_personal" type="email" value="{{ old('correo_personal', $perfil->correo_personal) }}" maxlength="150" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                @error('correo_personal') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="nivel_academico" class="mb-2 block text-sm font-semibold text-slate-700">Nivel académico</label>"""

content = content.replace(old, new)
open('resources/views/usuarios/edit-personal.blade.php', 'w', encoding='utf-8').write(content)
print('edit-personal updated')

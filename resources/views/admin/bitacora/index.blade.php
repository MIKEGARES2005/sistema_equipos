<x-admin-layout>
    <x-admin-shell>
        @php
            $etiquetasAccion = [
                'login' => 'Inicio de sesión',
                'logout' => 'Cierre de sesión',
                'crear_usuario' => 'Usuario creado',
                'actualizar_perfil' => 'Perfil actualizado',
                'cambiar_estado_usuario' => 'Estado de usuario cambiado',
                'recuperar_cuenta' => 'Cuenta recuperada',
                'crear_solicitud_registro' => 'Solicitud creada',
                'aprobar_solicitud_registro' => 'Solicitud aprobada',
                'rechazar_solicitud_registro' => 'Solicitud rechazada',
                'cambio_password' => 'Contraseña actualizada',
            ];
            $etiquetasEntidad = [
                'users' => 'Usuarios',
                'docentes' => 'Docentes',
                'estudiantes' => 'Estudiantes',
                'solicitudes_registro' => 'Solicitudes de registro',
                'gestiones' => 'Gestiones académicas',
                'materias' => 'Materias',
                'horarios' => 'Horarios',
                'inscripciones' => 'Inscripciones',
            ];
            $etiquetasCampo = [
                'username' => 'Usuario',
                'tipo_usuario' => 'Tipo de usuario',
                'estado' => 'Estado',
                'nombres' => 'Nombres',
                'apellido_paterno' => 'Apellido paterno',
                'apellido_materno' => 'Apellido materno',
                'correo_personal' => 'Correo personal',
                'correo_institucional' => 'Correo institucional',
                'telefono' => 'Teléfono',
                'direccion' => 'Dirección',
                'especialidad' => 'Especialidad',
                'cargo' => 'Cargo',
                'fecha_ingreso' => 'Fecha de ingreso',
                'generacion_ingreso' => 'Generación de ingreso',
                'matricula' => 'Matrícula',
            ];
            $formatearValor = function (mixed $valor): string {
                if (is_bool($valor)) {
                    return $valor ? 'Sí' : 'No';
                }

                if (is_array($valor)) {
                    return 'Información actualizada';
                }

                if ($valor === null || $valor === '') {
                    return 'Sin registro';
                }

                return (string) $valor;
            };
        @endphp

        <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
            <div class="space-y-6">
                <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-indigo-500">Seguridad / Auditoría</p>
                        <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Bitácora del sistema</h1>
                        <p class="mt-2 text-sm text-slate-500">Consulta el historial de accesos y acciones administrativas de forma clara y ordenada.</p>
                    </div>
                    <div class="flex items-center gap-2 rounded-2xl border border-indigo-100 bg-white px-4 py-3 shadow-sm">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <x-icon name="shield" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Registro protegido</p>
                            <p class="text-sm font-bold text-slate-700">Solo lectura</p>
                        </div>
                    </div>
                </header>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-[#1E2A5A] to-[#272F7A] p-5 text-white shadow-lg shadow-indigo-950/10">
                        <p class="text-xs font-bold uppercase tracking-wider text-indigo-200">Eventos encontrados</p>
                        <p class="mt-2 text-3xl font-black">{{ number_format($bitacoras->total()) }}</p>
                        <p class="mt-1 text-xs text-indigo-100/75">Según los filtros seleccionados</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Tipos de acción</p>
                        <p class="mt-2 text-3xl font-black text-slate-800">{{ $acciones->count() }}</p>
                        <p class="mt-1 text-xs text-slate-500">Acciones disponibles en el sistema</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Usuarios registrados</p>
                        <p class="mt-2 text-3xl font-black text-slate-800">{{ $usuarios->count() }}</p>
                        <p class="mt-1 text-xs text-slate-500">Cuentas disponibles para filtrar</p>
                    </div>
                </div>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <x-icon name="filter" class="h-4 w-4" />
                        </span>
                        <div>
                            <h2 class="text-sm font-black text-slate-800">Filtrar actividad</h2>
                            <p class="text-xs text-slate-500">Encuentra rápidamente un evento específico.</p>
                        </div>
                    </div>

                    <form method="GET" class="grid gap-3 lg:grid-cols-[1.2fr_1fr_1fr_auto] lg:items-end">
                        <div>
                            <label for="accion" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Acción</label>
                            <select id="accion" name="accion" class="block h-11 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-700 shadow-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                <option value="">Todas las acciones</option>
                                @foreach ($acciones as $accion)
                                    <option value="{{ $accion }}" @selected(($filtros['accion'] ?? '') === $accion)>{{ $etiquetasAccion[$accion] ?? str_replace('_', ' ', ucfirst($accion)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="rol" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Rol</label>
                            <select id="rol" name="rol" class="block h-11 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-700 shadow-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                <option value="">Todos los roles</option>
                                @foreach (['administrador' => 'Administrador', 'docente' => 'Docente', 'estudiante' => 'Estudiante'] as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected(($filtros['rol'] ?? '') === $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="user_id" class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Usuario</label>
                            <select id="user_id" name="user_id" class="block h-11 w-full rounded-xl border-slate-300 text-sm font-semibold text-slate-700 shadow-sm focus:border-[#4959A6] focus:ring-[#4959A6]">
                                <option value="">Todos los usuarios</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" @selected((string) ($filtros['user_id'] ?? '') === (string) $usuario->id)>{{ $usuario->username }} · {{ ucfirst($usuario->tipo_usuario) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#1E2A5A] to-[#272F7A] px-5 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:from-[#272F7A] hover:to-[#4959A6]">
                                <x-icon name="search" class="h-4 w-4" />
                                Filtrar
                            </button>
                            @if (count($filtros) > 0)
                                <a href="{{ route('admin.bitacora.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-slate-500 transition hover:bg-slate-50" title="Limpiar filtros">
                                    <x-icon name="refresh" class="h-4 w-4" />
                                </a>
                            @endif
                        </div>
                    </form>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-black text-slate-800">Actividad reciente</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Los eventos más recientes aparecen primero.</p>
                        </div>
                        <span class="inline-flex w-fit items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ $bitacoras->count() }} en esta página</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 font-black">Evento</th>
                                    <th class="px-5 py-3 font-black">Usuario</th>
                                    <th class="px-5 py-3 font-black">Entidad</th>
                                    <th class="px-5 py-3 font-black">Cambios registrados</th>
                                    <th class="px-5 py-3 font-black">Fecha / IP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($bitacoras as $bitacora)
                                    @php
                                        $antes = is_array($bitacora->antes) ? $bitacora->antes : [];
                                        $despues = is_array($bitacora->despues) ? $bitacora->despues : [];
                                        $campos = collect(array_unique(array_merge(array_keys($antes), array_keys($despues))))
                                            ->reject(fn ($campo) => in_array($campo, ['id', 'created_at', 'updated_at', 'remember_token'], true))
                                            ->filter(fn ($campo) => ! array_key_exists($campo, $antes) || $antes[$campo] !== ($despues[$campo] ?? null) || ! array_key_exists($campo, $despues))
                                            ->take(3);
                                    @endphp
                                    <tr class="align-top transition hover:bg-slate-50/70">
                                        <td class="px-5 py-4">
                                            <div class="flex items-start gap-3">
                                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                                    <x-icon name="activity" class="h-4 w-4" />
                                                </span>
                                                <div>
                                                    <p class="font-black text-slate-800">{{ $etiquetasAccion[$bitacora->accion] ?? str_replace('_', ' ', ucfirst($bitacora->accion)) }}</p>
                                                    <p class="mt-1 text-xs text-slate-400">{{ $bitacora->accion }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="font-bold text-slate-700">{{ $bitacora->user?->username ?? 'Invitado' }}</p>
                                            <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold capitalize text-slate-500">{{ $bitacora->rol ?? 'Sin sesión' }}</span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-slate-700">{{ $etiquetasEntidad[$bitacora->entidad] ?? ($bitacora->entidad ?? 'Sistema') }}</p>
                                            @if ($bitacora->entidad_id)
                                                <p class="mt-1 text-xs text-slate-400">Registro #{{ $bitacora->entidad_id }}</p>
                                            @endif
                                        </td>
                                        <td class="min-w-[260px] max-w-sm px-5 py-4">
                                            @if ($campos->isNotEmpty())
                                                <div class="space-y-2">
                                                    @foreach ($campos as $campo)
                                                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                                                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $etiquetasCampo[$campo] ?? str_replace('_', ' ', ucfirst($campo)) }}</p>
                                                            @if (array_key_exists($campo, $antes) && array_key_exists($campo, $despues) && $antes[$campo] !== $despues[$campo])
                                                                <p class="mt-1 text-xs text-slate-500"><span class="line-through">{{ $formatearValor($antes[$campo]) }}</span> <span class="px-1 text-slate-300">→</span> <strong class="text-slate-700">{{ $formatearValor($despues[$campo]) }}</strong></p>
                                                            @else
                                                                <p class="mt-1 text-xs font-semibold text-slate-700">{{ $formatearValor($despues[$campo] ?? $antes[$campo] ?? null) }}</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @if (count($antes) + count($despues) > $campos->count())
                                                    <p class="mt-2 text-[11px] font-semibold text-slate-400">Hay más datos registrados en este evento.</p>
                                                @endif
                                            @else
                                                <span class="text-xs text-slate-400">Sin datos adicionales</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <p class="font-semibold text-slate-600">{{ $bitacora->created_at?->format('d/m/Y H:i') }}</p>
                                            <p class="mt-1 font-mono text-[11px] text-slate-400">{{ $bitacora->ip ?? 'IP no disponible' }}</p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-14 text-center">
                                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><x-icon name="document" class="h-6 w-6" /></span>
                                            <p class="mt-3 text-sm font-bold text-slate-700">No hay eventos para mostrar</p>
                                            <p class="mt-1 text-xs text-slate-500">Prueba quitando algún filtro.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($bitacoras->hasPages())
                        <div class="border-t border-slate-100 px-5 py-4">{{ $bitacoras->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </x-admin-shell>
</x-admin-layout>

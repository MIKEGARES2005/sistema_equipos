<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Gestion;
use App\Models\SolicitudRegistro;
use App\Models\User;
use App\Services\BitacoraService;
use App\Services\NotificacionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class SolicitudRegistroController extends Controller
{
    /** Mostrar el formulario público de registro (1.er semestre). */
    public function createPublic(): View
    {
        return view('auth.solicitud-registro', [
            'generacionIngreso' => $this->generacionIngresoActual(),
        ]);
    }

    /** Mostrar el estado de una solicitud pública. */
    public function status(string $token): View
    {
        $solicitud = SolicitudRegistro::query()
            ->where('token_cancelacion', $token)
            ->firstOrFail();

        return view('auth.solicitud-estado', compact('solicitud'));
    }

    /** Consultar el estado de una solicitud desde la pantalla de espera. */
    public function statusData(string $token): JsonResponse
    {
        $solicitud = SolicitudRegistro::query()
            ->where('token_cancelacion', $token)
            ->firstOrFail();

        return response()->json([
            'estado' => $solicitud->estado,
            'ingreso_url' => $solicitud->estado === 'aprobada'
                ? route('registro.aspirante.login-approved', $token)
                : null,
        ]);
    }

    /** Iniciar automáticamente la sesión de una solicitud aprobada. */
    public function loginApproved(Request $request, string $token): JsonResponse
    {
        $solicitud = SolicitudRegistro::query()
            ->where('token_cancelacion', $token)
            ->where('estado', 'aprobada')
            ->with('usuarioCreado')
            ->firstOrFail();

        abort_unless($solicitud->usuarioCreado !== null, 409);

        Auth::guard('student')->login($solicitud->usuarioCreado);
        $request->session()->regenerate();
        $request->session()->flash('student_welcome', true);

        return response()->json([
            'redirect' => route('estudiante.dashboard', absolute: false),
        ]);
    }

    /** Almacenar la solicitud de registro pública. */
    public function storePublic(Request $request): RedirectResponse
    {
        $errores = [];
        $ci = trim((string) $request->input('ci'));
        $matricula = trim((string) $request->input('matricula'));
        $matricula = $matricula !== '' ? $matricula : null;

        if (Estudiante::query()->where('ci', $ci)->exists()) {
            $errores['ci'] = 'Este número de CI ya corresponde a un estudiante existente en el sistema.';
        }

        if ($matricula !== null && Estudiante::query()->where('matricula', $matricula)->exists()) {
            $errores['matricula'] = 'Esta matrícula ya corresponde a un estudiante existente en el sistema.';
        }

        $solicitudExistente = SolicitudRegistro::query()
            ->where('estado', '!=', 'cancelada')
            ->where(function ($query) use ($ci, $matricula): void {
                $query->where('ci', $ci);

                if ($matricula !== null) {
                    $query->orWhere('matricula', $matricula);
                }
            })
            ->first();

        if ($solicitudExistente) {
            $mensaje = $solicitudExistente->estado === 'pendiente'
                ? 'Ya existe una solicitud pendiente con esos datos.'
                : 'Esta persona ya tiene una solicitud procesada en el sistema.';

            if ($solicitudExistente->ci === $ci) {
                $errores['ci'] = $mensaje;
            }

            if ($matricula !== null && $solicitudExistente->matricula === $matricula) {
                $errores['matricula'] = $mensaje;
            }
        }

        if ($errores !== []) {
            return back()->withInput()->withErrors($errores);
        }

        $datos = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'ci' => [
                'required', 'string', 'max:20',
                Rule::unique('estudiantes', 'ci'),
                Rule::unique('solicitudes_registro', 'ci')->where('estado', 'pendiente'),
            ],
            'ci_extension' => ['required', Rule::in(['LP', 'OR', 'PT', 'CB', 'SC', 'TJ', 'CH', 'BE', 'PD'])],
            'telefono' => ['nullable', 'string', 'max:20'],
            'matricula' => [
                'nullable', 'string', 'max:30',
                Rule::unique('estudiantes', 'matricula'),
                Rule::unique('solicitudes_registro', 'matricula')->where('estado', 'pendiente'),
            ],
            'generacion_ingreso' => ['nullable', 'string', 'regex:/^(?:I|II)-\d{4}$/'],
            'correo_institucional' => ['nullable', 'email', 'max:150'],
            'correo_personal' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        $datos['semestre'] = 1;
        $datos['matricula'] = $datos['matricula'] ?? null;
        $datos['generacion_ingreso'] = $this->generacionIngresoActual();

        $solicitud = SolicitudRegistro::create([
            ...$datos,
            'token_cancelacion' => Str::random(64),
            'estado' => 'pendiente',
        ]);

        BitacoraService::registrar(
            'crear_solicitud_registro',
            'solicitudes_registro',
            $solicitud->id,
            null,
            [
                'estado' => 'pendiente',
                'nombres' => $solicitud->nombres,
                'apellido_paterno' => $solicitud->apellido_paterno,
                'matricula' => $solicitud->matricula,
            ]
        );

        NotificacionService::notificarAdmins(
            'solicitud',
            'Nueva solicitud de registro',
            "El estudiante {$solicitud->nombres} {$solicitud->apellido_paterno} envió una solicitud de cuenta de 1.er semestre.",
            route('admin.solicitudes.index', ['estado' => 'pendiente'])
        );

        return redirect()->route('registro.aspirante.status', $solicitud->token_cancelacion);
    }

    /** Cancelar una solicitud pública que todavía está pendiente. */
    public function cancel(string $token): RedirectResponse
    {
        $solicitud = SolicitudRegistro::query()
            ->where('token_cancelacion', $token)
            ->where('estado', 'pendiente')
            ->first();

        if (! $solicitud) {
            return redirect()->route('estudiante.login')
                ->with('error', 'La solicitud ya fue procesada o no existe.');
        }

        $solicitud->update(['estado' => 'cancelada']);

        return redirect()->route('estudiante.login')
            ->with('success', 'La solicitud fue cancelada correctamente.');
    }

    /** Listar solicitudes pendientes para el Administrador. */
    public function indexAdmin(Request $request): View
    {
        $estado = $request->query('estado', 'pendiente');

        $solicitudes = SolicitudRegistro::query()
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.solicitudes.index', [
            'solicitudes' => $solicitudes,
            'estado' => $estado,
            'resultado' => $request->session()->pull('registro_resultado'),
        ]);
    }

    /** Mostrar las credenciales temporales generadas tras una aprobación. */
    public function credentials(): View|RedirectResponse
    {
        $credenciales = session('registro_credenciales');

        if (! is_array($credenciales)) {
            return redirect()
                ->route('admin.solicitudes.index')
                ->with('registro_resultado', [
                    'tipo' => 'info',
                    'titulo' => 'Credenciales no disponibles',
                    'mensaje' => 'Las credenciales temporales ya no están disponibles.',
                ]);
        }

        return view('admin.solicitudes.credentials', compact('credenciales'));
    }

    /** Eliminar las credenciales temporales al cerrar la ventana de resultado. */
    public function clearCredentials(Request $request): RedirectResponse
    {
        $request->session()->forget('registro_credenciales');

        return redirect()->route('admin.solicitudes.index');
    }

    /** Aprobar varias solicitudes pendientes en una sola operación. */
    public function aprobarMultiples(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'distinct', 'exists:solicitudes_registro,id'],
        ]);

        $credenciales = [];
        $omitidas = 0;

        foreach ($datos['ids'] as $id) {
            $solicitud = SolicitudRegistro::find($id);

            if (! $solicitud || $solicitud->estado !== 'pendiente') {
                $omitidas++;

                continue;
            }

            $estudianteExistente = Estudiante::query()
                ->where(function ($query) use ($solicitud): void {
                    $query->where('ci', $solicitud->ci);

                    if ($solicitud->matricula !== null) {
                        $query->orWhere('matricula', $solicitud->matricula);
                    }
                })
                ->exists();

            $solicitudDuplicada = SolicitudRegistro::query()
                ->where('id', '!=', $solicitud->id)
                ->where('estado', '!=', 'cancelada')
                ->where(function ($query) use ($solicitud): void {
                    $query->where('ci', $solicitud->ci);

                    if ($solicitud->matricula !== null) {
                        $query->orWhere('matricula', $solicitud->matricula);
                    }
                })
                ->exists();

            if ($estudianteExistente || $solicitudDuplicada) {
                $omitidas++;

                continue;
            }

            try {
                $credenciales[] = $this->crearCuentaDesdeSolicitud($solicitud, $request);
            } catch (Throwable $e) {
                report($e);
                $omitidas++;
            }
        }

        if ($credenciales === []) {
            return back()->with('registro_resultado', [
                'tipo' => 'error',
                'titulo' => 'No se aprobaron solicitudes',
                'mensaje' => 'Revisa que las solicitudes sigan pendientes y que no estén duplicadas.',
            ]);
        }

        $mensaje = count($credenciales).' solicitud(es) aprobada(s) correctamente.';

        if ($omitidas > 0) {
            $mensaje .= " {$omitidas} solicitud(es) fueron omitidas por estar procesadas, duplicadas o presentar un error.";
        }

        return redirect()->route('admin.solicitudes.credentials')
            ->with('registro_credenciales', [
                'multiple' => true,
                'items' => $credenciales,
                'cantidad' => count($credenciales),
                'omitidas' => $omitidas,
                'mensaje' => $mensaje,
            ]);
    }

    /** Aprobar solicitud de registro (Crea estudiante y usuario de forma atómica). */
    public function aprobar(Request $request, int $id): RedirectResponse
    {
        $solicitud = SolicitudRegistro::findOrFail($id);

        if ($solicitud->estado !== 'pendiente') {
            return back()->with('registro_resultado', [
                'tipo' => 'warning',
                'titulo' => 'Solicitud ya procesada',
                'mensaje' => 'Esta solicitud ya fue procesada anteriormente.',
            ]);
        }

        $estudianteExistente = Estudiante::query()
            ->where(function ($query) use ($solicitud): void {
                $query->where('ci', $solicitud->ci);

                if ($solicitud->matricula !== null) {
                    $query->orWhere('matricula', $solicitud->matricula);
                }
            })
            ->first();

        if ($estudianteExistente) {
            return $this->rechazarSolicitudDuplicada($solicitud, $request);
        }

        $solicitudDuplicada = SolicitudRegistro::query()
            ->where('id', '!=', $solicitud->id)
            ->where('estado', '!=', 'cancelada')
            ->where(function ($query) use ($solicitud): void {
                $query->where('ci', $solicitud->ci);

                if ($solicitud->matricula !== null) {
                    $query->orWhere('matricula', $solicitud->matricula);
                }
            })
            ->first();

        if ($solicitudDuplicada) {
            return $this->rechazarSolicitudDuplicada($solicitud, $request);
        }

        $credenciales = $this->generarCredenciales($solicitud);
        $usuarioCreado = null;

        try {
            DB::transaction(function () use ($solicitud, $request, $credenciales, &$usuarioCreado): void {
                // 1. Crear el estudiante oficial (Semestre 1)
                $estudiante = Estudiante::create([
                    'nombres' => $solicitud->nombres,
                    'apellido_paterno' => $solicitud->apellido_paterno,
                    'apellido_materno' => $solicitud->apellido_materno,
                    'matricula' => $solicitud->matricula,
                    'semestre' => 1,
                    'generacion_ingreso' => $solicitud->generacion_ingreso,
                    'ci' => $solicitud->ci,
                    'ci_extension' => $solicitud->ci_extension,
                    'telefono' => $solicitud->telefono,
                    'correo_institucional' => $solicitud->correo_institucional,
                    'correo_personal' => $solicitud->correo_personal,
                    'direccion' => $solicitud->direccion,
                    'estado' => 'activo',
                ]);

                // 2. Crear la cuenta de usuario vinculada
                $usuarioCreado = User::create([
                    'username' => $credenciales['username'],
                    'password' => Hash::make($credenciales['password']),
                    'tipo_usuario' => 'estudiante',
                    'estudiante_id' => $estudiante->id,
                    'debe_cambiar_password' => true,
                ]);
                // 3. Actualizar la solicitud
                $solicitud->update([
                    'estado' => 'aprobada',
                    'revisado_por' => $request->user()->id,
                    'fecha_revision' => now(),
                    'usuario_creado_id' => $usuarioCreado->id,
                ]);

                BitacoraService::registrar(
                    'aprobar_solicitud_registro',
                    'solicitudes_registro',
                    $solicitud->id,
                    ['estado' => 'pendiente'],
                    ['estado' => 'aprobada', 'estudiante_id' => $estudiante->id]
                );
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('registro_resultado', [
                'tipo' => 'error',
                'titulo' => 'No se pudo aprobar la solicitud',
                'mensaje' => 'No fue posible completar el registro. Inténtalo nuevamente.',
            ]);
        }

        NotificacionService::notificarUsuario(
            (int) $usuarioCreado->id,
            'solicitud_aprobada',
            'Solicitud aceptada',
            'Tu solicitud fue aceptada. Al iniciar sesión, cambia tu usuario y contraseña y registra tus preguntas de seguridad en el panel de tu perfil.',
            route('estudiante.profile.edit')
        );

        return redirect()->route('admin.solicitudes.credentials')
            ->with('registro_credenciales', [
                'nombre' => trim($solicitud->nombres.' '.$solicitud->apellido_paterno),
                ...$credenciales,
            ]);
    }

    private function crearCuentaDesdeSolicitud(SolicitudRegistro $solicitud, Request $request): array
    {
        $credenciales = $this->generarCredenciales($solicitud);
        $usuarioCreado = null;

        DB::transaction(function () use ($solicitud, $request, $credenciales, &$usuarioCreado): void {
            $estudiante = Estudiante::create([
                'nombres' => $solicitud->nombres,
                'apellido_paterno' => $solicitud->apellido_paterno,
                'apellido_materno' => $solicitud->apellido_materno,
                'matricula' => $solicitud->matricula,
                'semestre' => 1,
                'generacion_ingreso' => $solicitud->generacion_ingreso,
                'ci' => $solicitud->ci,
                'ci_extension' => $solicitud->ci_extension,
                'telefono' => $solicitud->telefono,
                'correo_institucional' => $solicitud->correo_institucional,
                'correo_personal' => $solicitud->correo_personal,
                'direccion' => $solicitud->direccion,
                'estado' => 'activo',
            ]);

            $usuarioCreado = User::create([
                'username' => $credenciales['username'],
                'password' => Hash::make($credenciales['password']),
                'tipo_usuario' => 'estudiante',
                'estudiante_id' => $estudiante->id,
                'debe_cambiar_password' => true,
            ]);

            $solicitud->update([
                'estado' => 'aprobada',
                'revisado_por' => $request->user()->id,
                'fecha_revision' => now(),
                'usuario_creado_id' => $usuarioCreado->id,
            ]);

            BitacoraService::registrar(
                'aprobar_solicitud_registro',
                'solicitudes_registro',
                $solicitud->id,
                ['estado' => 'pendiente'],
                ['estado' => 'aprobada', 'estudiante_id' => $estudiante->id]
            );
        });

        NotificacionService::notificarUsuario(
            (int) $usuarioCreado->id,
            'solicitud_aprobada',
            'Solicitud aceptada',
            'Tu solicitud fue aceptada. Al iniciar sesiÃ³n, cambia tu usuario y contraseÃ±a y registra tus preguntas de seguridad en el panel de tu perfil.',
            route('estudiante.profile.edit')
        );

        return [
            'nombre' => trim($solicitud->nombres.' '.$solicitud->apellido_paterno),
            ...$credenciales,
        ];
    }

    /**
     * Generar las credenciales temporales para un estudiante aprobado.
     *
     * @return array{username: string, password: string}
     */
    private function generarCredenciales(SolicitudRegistro $solicitud): array
    {
        $nombres = $this->normalizarCredencial($solicitud->nombres);
        $apellidoPaterno = $this->normalizarCredencial($solicitud->apellido_paterno);
        $ci = $this->normalizarCredencial($solicitud->ci);
        $usernameBase = $nombres.'_'.$apellidoPaterno;
        $username = Str::limit($usernameBase, 50, '');
        $sufijo = 2;

        while (User::query()->where('username', $username)->exists()) {
            $sufijoTexto = '_'.$sufijo;
            $username = Str::limit($usernameBase, 50 - strlen($sufijoTexto), '').$sufijoTexto;
            $sufijo++;
        }

        return [
            'username' => $username,
            'password' => $apellidoPaterno.$ci,
        ];
    }

    private function normalizarCredencial(string $valor): string
    {
        $valor = Str::ascii(Str::lower(trim($valor)));

        return (string) preg_replace('/[^a-z0-9]/', '', $valor);
    }

    private function generacionIngresoActual(): string
    {
        $gestion = Gestion::vigente();

        if ($gestion?->codigo) {
            return $gestion->codigo;
        }

        $mes = now()->month;
        $periodo = $mes >= 2 && $mes <= 6 ? 'I' : 'II';

        return $periodo.'-'.now()->year;
    }

    /** Rechazar solicitud de registro. */
    public function rechazar(Request $request, int $id): RedirectResponse
    {
        $solicitud = SolicitudRegistro::findOrFail($id);

        if ($solicitud->estado !== 'pendiente') {
            return back()->with('registro_resultado', [
                'tipo' => 'warning',
                'titulo' => 'Solicitud ya procesada',
                'mensaje' => 'Esta solicitud ya fue procesada anteriormente.',
            ]);
        }

        $solicitud->update([
            'estado' => 'rechazada',
            'motivo_rechazo' => null,
            'revisado_por' => $request->user()->id,
            'fecha_revision' => now(),
        ]);

        BitacoraService::registrar(
            'rechazar_solicitud_registro',
            'solicitudes_registro',
            $solicitud->id,
            ['estado' => 'pendiente'],
            ['estado' => 'rechazada']
        );

        return redirect()->route('admin.solicitudes.index')
            ->with('registro_resultado', [
                'tipo' => 'rechazo',
                'titulo' => 'Solicitud cancelada',
                'mensaje' => 'Se canceló esta solicitud de registro.',
            ]);
    }

    private function rechazarSolicitudDuplicada(SolicitudRegistro $solicitud, Request $request): RedirectResponse
    {
        $solicitud->update([
            'estado' => 'rechazada',
            'motivo_rechazo' => null,
            'revisado_por' => $request->user()->id,
            'fecha_revision' => now(),
        ]);

        BitacoraService::registrar(
            'rechazar_solicitud_registro',
            'solicitudes_registro',
            $solicitud->id,
            ['estado' => 'pendiente'],
            ['estado' => 'rechazada', 'duplicada' => true]
        );

        return redirect()
            ->route('admin.solicitudes.index')
            ->with('registro_resultado', [
                'tipo' => 'rechazo',
                'titulo' => 'Solicitud cancelada',
                'mensaje' => 'Se canceló esta solicitud porque la persona ya tiene un registro o una solicitud procesada.',
            ]);
    }
}

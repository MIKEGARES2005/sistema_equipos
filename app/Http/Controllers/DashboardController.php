<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Bitacora;
use App\Models\ConfiguracionAcademica;
use App\Models\Diagnostico;
use App\Models\Docente;
use App\Models\Equipo;
use App\Models\Estudiante;
use App\Models\Gestion;
use App\Models\Heartbeat;
use App\Models\Horario;
use App\Models\Incidencia;
use App\Models\Inscripcion;
use App\Models\Invitado;
use App\Models\Materia;
use App\Models\Notificacion;
use App\Models\SolicitudRegistro;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->tipo_usuario === 'docente') {
            return $this->docenteDashboard($user);
        }

        $estadisticas = [
            'total_equipos' => Equipo::count(),
            'equipos_disponibles' => Equipo::query()
                ->whereHas('activo', fn (Builder $query) => $query->where('estado_uso', 'disponible'))
                ->count(),
            'asignaciones_activas' => Asignacion::whereRaw(
                'LOWER(estado) = ?',
                ['activa']
            )->count(),
            'incidencias_pendientes' => Incidencia::whereRaw(
                'LOWER(estado) IN (?, ?)',
                ['abierta', 'en_revision']
            )->count(),
            'total_estudiantes' => Estudiante::count(),
            'total_docentes' => Docente::count(),
        ];
        $ahora = now();
        $limiteHeartbeat = $ahora->copy()->subMinutes(Heartbeat::ONLINE_TIMEOUT_MINUTES);
        $estadisticas['equipos_conectados'] = Equipo::query()
            ->whereHas('heartbeats', fn (Builder $query) => $query->whereBetween('fecha_hora', [$limiteHeartbeat, $ahora]))
            ->count();
        $estadisticas['equipos_sin_datos'] = Equipo::doesntHave('heartbeats')->count();
        $estadisticas['equipos_desconectados'] = $estadisticas['total_equipos']
            - $estadisticas['equipos_conectados']
            - $estadisticas['equipos_sin_datos'];
        $ultimaActualizacionMonitoreo = Heartbeat::query()->max('fecha_hora');
        $diagnosticosCriticos = Diagnostico::query()
            ->with('equipo')
            ->where(function (Builder $query): void {
                $query->whereRaw('LOWER(nivel) = LOWER(?)', ['Crítico'])
                    ->orWhereRaw('LOWER(nivel) = LOWER(?)', ['Critico'])
                    ->orWhereRaw('LOWER(nivel) = LOWER(?)', ['Crítica'])
                    ->orWhereRaw('LOWER(nivel) = LOWER(?)', ['Critica']);
            })
            ->latest('fecha_hora')
            ->take(5)
            ->get();
        $asignacionesRecientes = Asignacion::with(['equipo', 'estudiante'])
            ->latest()
            ->take(5)
            ->get();
        $notificaciones = Notificacion::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->take(5)
            ->get();
        $notificacionesNoLeidas = Notificacion::query()
            ->where('user_id', $user->id)
            ->whereNull('leida_en')
            ->count();

        $gestionVigente = Gestion::vigente();
        $gestionVigenteCodigo = ConfiguracionAcademica::gestionVigente();

        $conteoPorSemestre = Estudiante::query()
            ->selectRaw('semestre, COUNT(*) as total')
            ->where('estado', 'activo')
            ->groupBy('semestre')
            ->pluck('total', 'semestre');

        $estudiantesPorSemestre = collect(range(1, 4))
            ->mapWithKeys(function (int $semestre) use ($conteoPorSemestre): array {
                $etiqueta = Materia::SEMESTRES[$semestre] ?? null;

                $total = (int) ($conteoPorSemestre[$semestre] ?? 0)
                    + ($etiqueta !== null ? (int) ($conteoPorSemestre[$etiqueta] ?? 0) : 0);

                return [$semestre => $total];
            });

        $estadisticas['estudiantes_activos'] = $estudiantesPorSemestre->sum();
        $estadisticas['docentes_activos'] = Docente::query()
            ->where('estado', 'activo')
            ->count();
        $estadisticas['administradores_activos'] = User::query()
            ->where('tipo_usuario', 'administrador')
            ->where('activo', true)
            ->count();
        $estadisticas['invitados_activos'] = Invitado::query()
            ->where('estado', 'activo')
            ->count();
        $estadisticas['solicitudes_pendientes'] = SolicitudRegistro::query()
            ->where('estado', 'pendiente')
            ->count();
        $estadisticas['materias_activas'] = Materia::query()
            ->where('estado', 'activo')
            ->count();
        $estadisticas['horarios_vigentes'] = Horario::query()
            ->where('gestion', $gestionVigenteCodigo)
            ->where('estado', 'activo')
            ->count();
        $estadisticas['inscripciones_vigentes'] = Inscripcion::query()
            ->where('gestion', $gestionVigenteCodigo)
            ->where('estado', 'Cursando')
            ->count();
        $estadisticas['cuentas_bloqueadas'] = User::query()
            ->whereNotNull('bloqueado_hasta')
            ->where('bloqueado_hasta', '>', now())
            ->count();

        $solicitudesRecientes = SolicitudRegistro::query()
            ->where('estado', 'pendiente')
            ->latest()
            ->take(4)
            ->get();

        $bitacoraReciente = Bitacora::query()
            ->with('user:id,username,tipo_usuario')
            ->latest('created_at')
            ->take(6)
            ->get();

        $ultimosAccesos = User::query()
            ->with([
                'docente:id,nombres,apellido_paterno,apellido_materno',
                'estudiante:id,nombres,apellido_paterno,apellido_materno,semestre',
            ])
            ->whereNotNull('ultimo_acceso')
            ->latest('ultimo_acceso')
            ->take(5)
            ->get();

        $maxEstudiantesSemestre = max(1, $estudiantesPorSemestre->max());

        return view('dashboard', compact(
            'estadisticas',
            'asignacionesRecientes',
            'ultimaActualizacionMonitoreo',
            'diagnosticosCriticos',
            'notificaciones',
            'notificacionesNoLeidas',
            'gestionVigente',
            'estudiantesPorSemestre',
            'maxEstudiantesSemestre',
            'solicitudesRecientes',
            'bitacoraReciente',
            'ultimosAccesos',
        ));
    }

    private function docenteDashboard(User $user): View
    {
        $horarios = DB::table('horarios')
            ->join('materias', 'horarios.materia_id', '=', 'materias.id')
            ->where('horarios.docente_id', $user->docente_id)
            ->whereRaw('LOWER(horarios.estado) = ?', ['activo'])
            ->whereRaw('LOWER(materias.estado) = ?', ['activo'])
            ->orderBy('horarios.dia_semana')
            ->orderBy('horarios.hora_inicio')
            ->get([
                'horarios.id',
                'horarios.materia_id',
                'horarios.dia_semana',
                'horarios.hora_inicio',
                'horarios.hora_fin',
                'horarios.aula',
                'materias.nombre as materia_nombre',
                'materias.semestre',
            ]);

        $materiaIds = $horarios->pluck('materia_id')->unique();
        $gestionVigenteCodigo = ConfiguracionAcademica::gestionVigente();

        $asignacionesActivas = Asignacion::query()
            ->forTeacher($user)
            ->whereRaw('LOWER(estado) = ?', ['activa']);
        $asignacionesRecientes = Asignacion::query()
            ->forTeacher($user)
            ->with(['equipo', 'estudiante'])
            ->latest()
            ->take(5)
            ->get();
        $equiposDocente = Equipo::query()
            ->whereIn(
                'id',
                Asignacion::forTeacher($user)->select('equipo_id')->distinct()
            );
        $ahora = now();
        $limiteHeartbeat = $ahora->copy()->subMinutes(Heartbeat::ONLINE_TIMEOUT_MINUTES);
        $equiposConectados = (clone $equiposDocente)
            ->whereHas('heartbeats', fn (Builder $query) => $query->whereBetween('fecha_hora', [$limiteHeartbeat, $ahora]))
            ->count();
        $equiposSinDatos = (clone $equiposDocente)->doesntHave('heartbeats')->count();
        $totalEquiposDocente = (clone $equiposDocente)->count();
        $ultimaActualizacionMonitoreo = Heartbeat::query()
            ->whereIn('equipo_id', (clone $equiposDocente)->select('id'))
            ->max('fecha_hora');
        $diagnosticosCriticos = Diagnostico::query()
            ->with('equipo')
            ->whereIn('equipo_id', (clone $equiposDocente)->select('id'))
            ->where(function (Builder $query): void {
                $query->whereRaw('LOWER(nivel) = LOWER(?)', ['Crítico'])
                    ->orWhereRaw('LOWER(nivel) = LOWER(?)', ['Critico'])
                    ->orWhereRaw('LOWER(nivel) = LOWER(?)', ['Crítica'])
                    ->orWhereRaw('LOWER(nivel) = LOWER(?)', ['Critica']);
            })
            ->latest('fecha_hora')
            ->take(5)
            ->get();

        $estadisticas = [
            'materias' => $materiaIds->count(),
            'estudiantes' => DB::table('inscripciones')
                ->whereIn('materia_id', $materiaIds)
                ->where('gestion', $gestionVigenteCodigo)
                ->where('estado', 'Cursando')
                ->distinct()
                ->count('estudiante_id'),
            'asignaciones_activas' => (clone $asignacionesActivas)->count(),
            'equipos_en_clase' => (clone $asignacionesActivas)
                ->distinct()
                ->count('equipo_id'),
            'equipos_conectados' => $equiposConectados,
            'equipos_desconectados' => $totalEquiposDocente - $equiposConectados - $equiposSinDatos,
            'equipos_sin_datos' => $equiposSinDatos,
            'incidencias_pendientes' => Incidencia::query()
                ->whereIn('asignacion_id', (clone $asignacionesActivas)->select('id'))
                ->whereIn('estado', ['abierta', 'en_revision'])
                ->count(),
        ];

        return view('dashboard-docente', compact(
            'horarios',
            'estadisticas',
            'asignacionesRecientes',
            'gestionVigenteCodigo',
            'ultimaActualizacionMonitoreo',
            'diagnosticosCriticos',
        ));
    }
}

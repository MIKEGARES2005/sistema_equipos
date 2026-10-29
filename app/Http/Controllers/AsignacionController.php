<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Equipo;
use App\Models\Estudiante;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AsignacionController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['activa', 'finalizada'])],
        ]);
        $busqueda = trim($filtros['buscar'] ?? '');
        $estado = strtolower(trim((string) ($filtros['estado'] ?? '')));

        $asignaciones = $this->visibleAssignments()->with([
            'equipo',
            'estudiante',
            'materia',
            'horario',
        ])
            ->when($busqueda !== '', function (Builder $query) use ($busqueda): void {
                $query->where(function (Builder $assignmentQuery) use ($busqueda): void {
                    $assignmentQuery
                        ->whereHas('equipo', function (Builder $equipmentQuery) use ($busqueda): void {
                            $equipmentQuery->where('codigo', 'like', "%{$busqueda}%")
                                ->orWhere('tipo_equipo', 'like', "%{$busqueda}%")
                                ->orWhere('marca', 'like', "%{$busqueda}%")
                                ->orWhere('modelo', 'like', "%{$busqueda}%");
                        })
                        ->orWhereHas('estudiante', function (Builder $studentQuery) use ($busqueda): void {
                            $studentQuery->where('nombres', 'like', "%{$busqueda}%")
                                ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                                ->orWhere('apellido_materno', 'like', "%{$busqueda}%")
                                ->orWhere('matricula', 'like', "%{$busqueda}%");
                        })
                        ->orWhereHas('materia', function (Builder $subjectQuery) use ($busqueda): void {
                            $subjectQuery->where('nombre', 'like', "%{$busqueda}%")
                                ->orWhere('sigla', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->when($estado !== '', function (Builder $query) use ($estado): void {
                $query->whereRaw('LOWER(estado) = ?', [$estado]);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('asignaciones.index', compact('asignaciones'));
    }

    public function create(): View
    {
        $equipos = Equipo::where(function ($query) {
            $query->where('estado', 'Disponible')
                ->orWhere(function ($groupEquipmentQuery): void {
                    $groupEquipmentQuery->where('modo_uso', 'grupal')
                        ->where('estado', 'En uso');
                });
        })->get();
        $horarios = $this->availableSchedules()
            ->orderBy('horarios.dia_semana')
            ->orderBy('horarios.hora_inicio')
            ->get();
        $materiaIds = $horarios->pluck('materia_id')->unique();
        $inscripcionesActivas = DB::table('inscripciones')
            ->select('estudiante_id')
            ->whereIn('materia_id', $materiaIds)
            ->where('estado', 'Cursando');
        $estudiantes = Estudiante::where('estado', 'activo')
            ->whereIn('id', $inscripcionesActivas)
            ->orderBy('nombres')
            ->get();

        return view('asignaciones.create', compact(
            'equipos',
            'estudiantes',
            'horarios'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'equipo_id' => ['required', 'exists:equipos,id'],
            'estudiante_id' => ['required', 'exists:estudiantes,id'],
            'horario_id' => ['required', 'exists:horarios,id'],
            'grupo' => ['nullable', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $horario = $this->availableSchedules()
            ->where('horarios.id', $datos['horario_id'])
            ->first();

        if ($horario === null) {
            return back()
                ->withInput()
                ->withErrors(['horario_id' => 'El horario no pertenece a una materia autorizada.']);
        }

        $inscripcionActiva = DB::table('inscripciones')
            ->where('estudiante_id', $datos['estudiante_id'])
            ->where('materia_id', $horario->materia_id)
            ->where('estado', 'Cursando')
            ->exists();

        if (! $inscripcionActiva) {
            return back()
                ->withInput()
                ->withErrors(['estudiante_id' => 'El estudiante no tiene una inscripción activa en esta materia.']);
        }

        $equipo = Equipo::findOrFail($datos['equipo_id']);

        $equipoDisponible = $equipo->estado === 'Disponible'
            || ($equipo->modo_uso === 'grupal' && $equipo->estado === 'En uso');

        if (! $equipoDisponible) {
            return back()
                ->withInput()
                ->withErrors([
                    'equipo_id' => 'El equipo seleccionado no está disponible para una nueva asignación.',
                ]);
        }

        if ($equipo->modo_uso === 'individual') {
            $asignacionActiva = Asignacion::where('equipo_id', $equipo->id)
                ->where('estado', 'activa')
                ->exists();

            if ($asignacionActiva) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'equipo_id' => 'Este equipo de uso individual ya tiene una asignación activa.',
                    ]);
            }
        }

        $datos['materia_id'] = $horario->materia_id;
        $datos['horario_id'] = $horario->id;
        $datos['origen'] = 'panel_web';
        $datos['fecha_solicitud'] = now();
        $datos['fecha_aprobacion'] = now();
        $datos['estado'] = 'activa';

        DB::transaction(function () use ($datos, $equipo): void {
            Asignacion::create($datos);
            $equipo->update([
                'estado' => 'En uso',
            ]);
        });

        return redirect()
            ->route('asignaciones.index')
            ->with('success', 'Asignación creada correctamente.');
    }

    public function finalizar(int $id): RedirectResponse
    {
        $asignacion = $this->visibleAssignments()->findOrFail($id);

        if ($asignacion->estado !== 'activa') {
            return back()->withErrors([
                'asignacion' => 'La asignación seleccionada no está activa.',
            ]);
        }

        DB::transaction(function () use ($asignacion): void {
            $asignacion->update([
                'estado' => 'finalizada',
                'fecha_fin' => now(),
            ]);

            $equipo = $asignacion->equipo;

            if ($equipo->modo_uso === 'individual') {
                $equipo->update([
                    'estado' => 'Disponible',
                ]);
            } else {
                $asignacionesActivas = Asignacion::where('equipo_id', $equipo->id)
                    ->where('estado', 'activa')
                    ->exists();
                $equipo->update([
                    'estado' => $asignacionesActivas ? 'En uso' : 'Disponible',
                ]);
            }
        });

        return redirect()
            ->route('asignaciones.index')
            ->with('success', 'Asignación finalizada correctamente.');
    }

    private function availableSchedules(): QueryBuilder
    {
        $query = DB::table('horarios')
            ->join('materias', 'materias.id', '=', 'horarios.materia_id')
            ->whereRaw('LOWER(horarios.estado) = ?', ['activo'])
            ->whereRaw('LOWER(materias.estado) = ?', ['activo'])
            ->select([
                'horarios.id',
                'horarios.docente_id',
                'horarios.materia_id',
                'horarios.dia_semana',
                'horarios.hora_inicio',
                'horarios.hora_fin',
                'horarios.aula',
                'materias.nombre as materia_nombre',
                'materias.semestre',
            ]);

        if (auth()->user()->isDocente()) {
            $query->where('horarios.docente_id', auth()->user()->docente_id);
        }

        return $query;
    }

    private function visibleAssignments(): Builder
    {
        $query = Asignacion::query();

        if (auth()->user()->isDocente()) {
            $query->whereIn(
                'horario_id',
                DB::table('horarios')
                    ->where('docente_id', auth()->user()->docente_id)
                    ->select('id')
            );
        }

        return $query;
    }
}

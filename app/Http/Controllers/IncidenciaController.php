<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Equipo;
use App\Models\Incidencia;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IncidenciaController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['pendientes', 'resueltas'])],
            'prioridad' => ['nullable', Rule::in(['Baja', 'Media', 'Alta', 'Critica'])],
        ]);
        $busqueda = trim($filtros['buscar'] ?? '');

        $incidencias = $this->visibleIncidents()->with([
            'equipo',
            'asignacion.estudiante',
        ])
            ->when($busqueda !== '', function (Builder $query) use ($busqueda): void {
                $query->where(function (Builder $incidentQuery) use ($busqueda): void {
                    $incidentQuery->where('tipo', 'like', "%{$busqueda}%")
                        ->orWhere('descripcion', 'like', "%{$busqueda}%")
                        ->orWhere('estado', 'like', "%{$busqueda}%")
                        ->orWhereHas('equipo', function (Builder $equipmentQuery) use ($busqueda): void {
                            $equipmentQuery->where('codigo', 'like', "%{$busqueda}%")
                                ->orWhere('marca', 'like', "%{$busqueda}%")
                                ->orWhere('modelo', 'like', "%{$busqueda}%");
                        })
                        ->orWhereHas('asignacion.estudiante', function (Builder $studentQuery) use ($busqueda): void {
                            $studentQuery->where('nombres', 'like', "%{$busqueda}%")
                                ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                                ->orWhere('apellido_materno', 'like', "%{$busqueda}%")
                                ->orWhere('matricula', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->when(isset($filtros['estado']) && $filtros['estado'] !== '', function (Builder $query) use ($filtros): void {
                if ($filtros['estado'] === 'resueltas') {
                    $query->whereRaw('LOWER(estado) = ?', ['resuelta']);

                    return;
                }

                $query->whereRaw('LOWER(estado) IN (?, ?, ?, ?)', [
                    'reportada',
                    'en revision',
                    'pendiente',
                    'abierta',
                ]);
            })
            ->when(isset($filtros['prioridad']) && $filtros['prioridad'] !== '', function (Builder $query) use ($filtros): void {
                $query->whereRaw('LOWER(prioridad) = ?', [strtolower($filtros['prioridad'])]);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('incidencias.index', compact('incidencias'));
    }

    public function create(): View
    {
        $esDocente = auth()->user()->isDocente();
        $asignaciones = ($esDocente
            ? $this->teacherAssignments(true)
            : Asignacion::query()->where('estado', 'activa'))
            ->with([
                'equipo',
                'estudiante',
            ])
            ->latest()
            ->get();

        $equipos = $esDocente
            ? Equipo::whereIn(
                'id',
                $this->teacherAssignments(true)->select('equipo_id')
            )->orderBy('codigo')->get()
            : Equipo::orderBy('codigo')->get();

        return view('incidencias.create', compact(
            'equipos',
            'asignaciones',
            'esDocente'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $esDocente = $request->user()->isDocente();
        $datos = $request->validate([
            'equipo_id' => [$esDocente ? 'nullable' : 'required', 'exists:equipos,id'],
            'asignacion_id' => [$esDocente ? 'required' : 'nullable', 'exists:asignaciones,id'],
            'tipo' => ['required', Rule::in(['Hardware', 'Software', 'Red', 'Bateria', 'Pantalla', 'Periferico', 'Otro'])],
            'descripcion' => ['required', 'string', 'max:5000'],
            'prioridad' => ['required', Rule::in(['Baja', 'Media', 'Alta', 'Critica'])],
            'estado' => [$esDocente ? 'nullable' : 'required', Rule::in(['Reportada', 'En revision'])],
            'fecha_reporte' => [$esDocente ? 'nullable' : 'required', 'date'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $asignacion = null;
        if (! empty($datos['asignacion_id'])) {
            $consultaAsignacion = $esDocente
                ? $this->teacherAssignments(true)
                : Asignacion::query();
            $asignacion = $consultaAsignacion->find($datos['asignacion_id']);

            if ($asignacion === null) {
                return back()
                    ->withInput()
                    ->withErrors(['asignacion_id' => 'La asignación no está dentro de tu ámbito autorizado.']);
            }

            if (
                isset($datos['equipo_id'])
                && (int) $datos['equipo_id'] !== (int) $asignacion->equipo_id
            ) {
                return back()
                    ->withInput()
                    ->withErrors(['equipo_id' => 'El equipo no coincide con la asignación seleccionada.']);
            }

            $datos['equipo_id'] = $asignacion->equipo_id;
        }

        if ($esDocente) {
            $datos['estado'] = 'Reportada';
            $datos['fecha_reporte'] = now();
            $datos['fecha_resolucion'] = null;
        }

        Incidencia::create($datos);

        return redirect()
            ->route('incidencias.index')
            ->with('success', 'Incidencia registrada correctamente.');
    }

    public function resolver(int $id): RedirectResponse
    {
        $incidencia = Incidencia::findOrFail($id);

        if (strtolower($incidencia->estado) === 'resuelta') {
            return back()->withErrors([
                'incidencia' => 'La incidencia ya está resuelta.',
            ]);
        }

        $incidencia->update([
            'estado' => 'Resuelta',
            'fecha_resolucion' => now(),
        ]);

        return redirect()
            ->route('incidencias.index')
            ->with('success', 'Incidencia marcada como resuelta.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $incidencia = Incidencia::findOrFail($id);
        $incidencia->delete();

        return redirect()
            ->route('incidencias.index')
            ->with('success', 'Incidencia eliminada correctamente.');
    }

    private function visibleIncidents(): Builder
    {
        $query = Incidencia::query();

        if (auth()->user()->isDocente()) {
            $query->whereIn(
                'asignacion_id',
                $this->teacherAssignments()->select('id')
            );
        }

        return $query;
    }

    private function teacherAssignments(bool $activeOnly = false): Builder
    {
        $horarios = DB::table('horarios')
            ->where('docente_id', auth()->user()->docente_id);

        if ($activeOnly) {
            $horarios->where('estado', 'activo');
        }

        $query = Asignacion::query()
            ->whereIn(
                'horario_id',
                $horarios->select('id')
            );

        if ($activeOnly) {
            $query->where('estado', 'activa');
        }

        return $query;
    }
}

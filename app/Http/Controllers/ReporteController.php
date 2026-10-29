<?php

namespace App\Http\Controllers;

use App\Models\Accesorio;
use App\Models\Asignacion;
use App\Models\Equipo;
use App\Models\Incidencia;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $equipos = Equipo::query();
        $asignaciones = Asignacion::query()
            ->when($filtros['desde'] ?? null, fn (Builder $query, string $desde) => $query->whereDate('fecha_inicio', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn (Builder $query, string $hasta) => $query->whereDate('fecha_inicio', '<=', $hasta));
        $incidencias = Incidencia::query()
            ->when($filtros['desde'] ?? null, fn (Builder $query, string $desde) => $query->whereDate('fecha_reporte', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn (Builder $query, string $hasta) => $query->whereDate('fecha_reporte', '<=', $hasta));
        $asignacionesDocente = Asignacion::query();
        $accesorios = Accesorio::query();

        if ($request->user()->isDocente()) {
            $asignacionesDocente->forTeacher($request->user());
            $equipos->whereIn(
                'id',
                (clone $asignacionesDocente)->select('equipo_id')->distinct()
            );
            $asignaciones->forTeacher($request->user());
            $incidencias->whereIn(
                'asignacion_id',
                (clone $asignacionesDocente)->select('id')
            );
            $accesorios->whereIn(
                'equipo_id',
                (clone $equipos)->select('id')
            );
        }

        $totalEquipos = (clone $equipos)->count();
        $equiposDisponibles = (clone $equipos)->whereRaw('LOWER(estado) = ?', ['disponible'])->count();
        $equiposEnUso = (clone $equipos)->whereRaw('LOWER(estado) = ?', ['en uso'])->count();
        $equiposOtros = (clone $equipos)->whereRaw('LOWER(estado) NOT IN (?, ?)', ['disponible', 'en uso'])->count();
        $totalAsignaciones = (clone $asignaciones)->count();
        $asignacionesActivas = (clone $asignaciones)->whereRaw('LOWER(estado) = ?', ['activa'])->count();
        $asignacionesFinalizadas = (clone $asignaciones)->whereRaw('LOWER(estado) = ?', ['finalizada'])->count();
        $totalIncidencias = (clone $incidencias)->count();
        $incidenciasResueltas = (clone $incidencias)->whereRaw('LOWER(estado) = ?', ['resuelta'])->count();
        $incidenciasPendientes = $totalIncidencias - $incidenciasResueltas;
        $totalAccesorios = $accesorios->count();

        $asignacionesRecientes = (clone $asignaciones)->with([
            'equipo',
            'estudiante',
            'materia',
        ])
            ->latest('fecha_inicio')
            ->take(5)
            ->get();
        $incidenciasRecientes = (clone $incidencias)->with([
            'equipo',
            'asignacion.estudiante',
        ])
            ->latest('fecha_reporte')
            ->take(5)
            ->get();
        $estadisticas = compact(
            'totalEquipos',
            'equiposDisponibles',
            'equiposEnUso',
            'equiposOtros',
            'totalAsignaciones',
            'asignacionesActivas',
            'asignacionesFinalizadas',
            'totalIncidencias',
            'incidenciasPendientes',
            'incidenciasResueltas',
            'totalAccesorios',
        );

        return view('reportes.index', compact('estadisticas', 'asignacionesRecientes', 'incidenciasRecientes', 'filtros'));
    }
}

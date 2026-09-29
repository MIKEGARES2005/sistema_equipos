<?php
namespace App\Http\Controllers;
use App\Models\Accesorio;
use App\Models\Asignacion;
use App\Models\Equipo;
use App\Models\Incidencia;
class ReporteController extends Controller
{
    public function index()
    {
        $totalEquipos = Equipo::count();
        $equiposDisponibles = Equipo::where('estado', 'Disponible')->count();
        $equiposEnUso = Equipo::where('estado', 'En uso')->count();
        $equiposOtros = Equipo::whereNotIn('estado', [
            'Disponible',
            'En uso',
        ])->count();
        $totalAsignaciones = Asignacion::count();
        $asignacionesActivas = Asignacion::where('estado', 'activa')->count();
        $asignacionesFinalizadas = Asignacion::where(
            'estado',
            'finalizada'
        )->count();
        $totalIncidencias = Incidencia::count();
        $incidenciasPendientes = Incidencia::where(
            'estado',
            '!=',
            'Resuelta'
        )->count();
        $incidenciasResueltas = Incidencia::where(
            'estado',
            'Resuelta'
        )->count();
        $totalAccesorios = Accesorio::count();
        $asignacionesRecientes = Asignacion::with([
            'equipo',
            'estudiante',
            'materia',
        ])
        ->latest()
        ->take(5)
        ->get();
        $incidenciasRecientes = Incidencia::with([
            'equipo',
            'asignacion.estudiante',
        ])
        ->latest()
        ->take(5)
        ->get();
        return view('reportes.index', compact(
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
            'asignacionesRecientes',
            'incidenciasRecientes'
        ));
    }
}
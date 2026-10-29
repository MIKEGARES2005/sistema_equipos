<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionAcademica;
use App\Models\Inscripcion;
use App\Models\Materia;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PanelEstudiantilController extends Controller
{
    public function index(Request $request): View
    {
        $docenteId = $request->user()->docente_id;
        $materias = Materia::query()
            ->whereRaw('LOWER(estado) = ?', ['activo'])
            ->whereHas('horarios', function (Builder $query) use ($docenteId): void {
                $query->where('docente_id', $docenteId)
                    ->whereRaw('LOWER(estado) = ?', ['activo']);
            })
            ->orderBy('semestre')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'sigla', 'semestre']);
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'materia_id' => ['nullable', 'integer', Rule::in($materias->modelKeys())],
        ]);
        $buscar = trim($filtros['buscar'] ?? '');
        $gestion = ConfiguracionAcademica::gestionVigente();

        $inscripcionesQuery = Inscripcion::query()
            ->with([
                'estudiante:id,nombres,apellido_paterno,apellido_materno,matricula,semestre,estado',
                'materia:id,nombre,sigla,semestre',
            ])
            ->where('gestion', $gestion)
            ->where('estado', 'Cursando')
            ->whereIn('materia_id', $materias->modelKeys())
            ->when($filtros['materia_id'] ?? null, fn (Builder $query, int $materiaId) => $query->where('materia_id', $materiaId))
            ->when($buscar !== '', function (Builder $query) use ($buscar): void {
                $query->where(function (Builder $estudiantesQuery) use ($buscar): void {
                    $estudiantesQuery->whereHas('estudiante', function (Builder $estudianteQuery) use ($buscar): void {
                        $estudianteQuery->where('nombres', 'like', "%{$buscar}%")
                            ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                            ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                            ->orWhere('matricula', 'like', "%{$buscar}%");
                    })->orWhereHas('materia', function (Builder $materiaQuery) use ($buscar): void {
                        $materiaQuery->where('nombre', 'like', "%{$buscar}%")
                            ->orWhere('sigla', 'like', "%{$buscar}%");
                    });
                });
            });
        $totalInscripciones = (clone $inscripcionesQuery)->count();
        $totalEstudiantes = (clone $inscripcionesQuery)
            ->distinct()
            ->count('estudiante_id');
        $inscripciones = $inscripcionesQuery
            ->orderBy('materia_id')
            ->orderBy('estudiante_id')
            ->paginate(15)
            ->withQueryString();

        return view('panel-docente.estudiantes', compact(
            'filtros',
            'gestion',
            'inscripciones',
            'materias',
            'totalEstudiantes',
            'totalInscripciones',
        ));
    }
}

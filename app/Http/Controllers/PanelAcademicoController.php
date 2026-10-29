<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PanelAcademicoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);
        $buscar = trim($filtros['buscar'] ?? '');

        $docentes = Docente::query()
            ->select([
                'id',
                'nombres',
                'apellido_paterno',
                'apellido_materno',
                'ci',
                'ci_extension',
                'telefono',
                'cargo',
                'nivel_academico',
                'fecha_ingreso',
                'estado',
                'foto_personal',
            ])
            ->whereHas('users', fn (Builder $query) => $query->where('tipo_usuario', 'docente'))
            ->when($buscar !== '', function (Builder $query) use ($buscar): void {
                $query->where(function (Builder $docentesQuery) use ($buscar): void {
                    $docentesQuery->where('nombres', 'like', "%{$buscar}%")
                        ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                        ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                        ->orWhere('ci', 'like', "%{$buscar}%")
                        ->orWhere('telefono', 'like', "%{$buscar}%")
                        ->orWhere('cargo', 'like', "%{$buscar}%")
                        ->orWhere('nivel_academico', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->paginate(12)
            ->withQueryString();

        return view('panel-docente.academico', compact('docentes', 'filtros'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Diagnostico;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DiagnosticoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', 'string', 'max:30'],
            'nivel' => ['nullable', 'string', 'max:20'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);
        $buscar = trim($filtros['buscar'] ?? '');

        $diagnosticosQuery = Diagnostico::query();
        if ($request->user()->isDocente()) {
            $diagnosticosQuery->whereIn(
                'equipo_id',
                Asignacion::forTeacher($request->user())->select('equipo_id')->distinct()
            );
        }

        $diagnosticos = (clone $diagnosticosQuery)
            ->with(['equipo', 'heartbeat'])
            ->when($buscar !== '', function (Builder $query) use ($buscar): void {
                $query->where(function (Builder $diagnosticoQuery) use ($buscar): void {
                    $diagnosticoQuery->where('descripcion', 'like', "%{$buscar}%")
                        ->orWhere('tipo', 'like', "%{$buscar}%")
                        ->orWhere('nivel', 'like', "%{$buscar}%")
                        ->orWhereHas('equipo', function (Builder $equipoQuery) use ($buscar): void {
                            $equipoQuery->where('codigo', 'like', "%{$buscar}%")
                                ->orWhere('marca', 'like', "%{$buscar}%")
                                ->orWhere('modelo', 'like', "%{$buscar}%");
                        });
                });
            })
            ->when($filtros['tipo'] ?? null, fn (Builder $query, string $tipo) => $query->whereRaw('LOWER(tipo) = LOWER(?)', [$tipo]))
            ->when($filtros['nivel'] ?? null, fn (Builder $query, string $nivel) => $query->whereRaw('LOWER(nivel) = LOWER(?)', [$nivel]))
            ->when($filtros['desde'] ?? null, fn (Builder $query, string $desde) => $query->whereDate('fecha_hora', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn (Builder $query, string $hasta) => $query->whereDate('fecha_hora', '<=', $hasta))
            ->latest('fecha_hora')
            ->paginate(12)
            ->withQueryString();

        $niveles = (clone $diagnosticosQuery)
            ->select('nivel')
            ->distinct()
            ->orderBy('nivel')
            ->pluck('nivel');
        $tipos = (clone $diagnosticosQuery)
            ->select('tipo')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        return view('diagnosticos.index', compact('diagnosticos', 'filtros', 'niveles', 'tipos'));
    }
}

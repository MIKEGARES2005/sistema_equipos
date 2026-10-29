<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Equipo;
use App\Models\Heartbeat;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class MonitoreoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['conectados', 'desconectados', 'sin_datos'])],
        ]);

        $ahora = now();
        $desde = $ahora->copy()->subMinutes(Heartbeat::ONLINE_TIMEOUT_MINUTES);
        $equiposQuery = Equipo::query();

        if ($request->user()->isDocente()) {
            $equiposAsignados = Asignacion::forTeacher($request->user())
                ->select('equipo_id')
                ->distinct();
            $equiposQuery->whereIn('id', $equiposAsignados);
        }

        $equiposConectados = Equipo::query()
            ->when($request->user()->isDocente(), fn (Builder $query) => $query->whereIn(
                'id',
                Asignacion::forTeacher($request->user())->select('equipo_id')->distinct()
            ))
            ->whereHas('heartbeats', fn (Builder $query) => $query->whereBetween('created_at', [$desde, $ahora]));
        $equiposSinDatos = (clone $equiposQuery)->doesntHave('heartbeats');
        $totalEquipos = (clone $equiposQuery)->count();
        $totalConectados = (clone $equiposConectados)->count();
        $totalSinDatos = (clone $equiposSinDatos)->count();
        $estadisticas = [
            'total' => $totalEquipos,
            'conectados' => $totalConectados,
            'desconectados' => $totalEquipos - $totalConectados - $totalSinDatos,
            'sin_datos' => $totalSinDatos,
        ];

        $equipos = (clone $equiposQuery)
            ->with('ultimoHeartbeat')
            ->when(($filtros['buscar'] ?? '') !== '', function (Builder $query) use ($filtros): void {
                $buscar = trim($filtros['buscar']);
                $query->where(function (Builder $equipoQuery) use ($buscar): void {
                    $equipoQuery->where('codigo', 'like', "%{$buscar}%")
                        ->orWhere('marca', 'like', "%{$buscar}%")
                        ->orWhere('modelo', 'like', "%{$buscar}%")
                        ->orWhere('numero_serie', 'like', "%{$buscar}%");
                });
            })
            ->when(($filtros['estado'] ?? '') !== '', function (Builder $query) use ($filtros, $desde, $ahora): void {
                if ($filtros['estado'] === 'conectados') {
                    $query->whereHas('heartbeats', fn (Builder $heartbeatQuery) => $heartbeatQuery->whereBetween('created_at', [$desde, $ahora]));

                    return;
                }

                if ($filtros['estado'] === 'sin_datos') {
                    $query->doesntHave('heartbeats');

                    return;
                }

                $query->whereHas('heartbeats')
                    ->whereDoesntHave('heartbeats', fn (Builder $heartbeatQuery) => $heartbeatQuery->whereBetween('created_at', [$desde, $ahora]));
            })
            ->orderBy('codigo')
            ->paginate(12)
            ->withQueryString();

        $equipos->getCollection()->each(function (Equipo $equipo) use ($desde, $ahora): void {
            $ultimaFecha = $equipo->ultimoHeartbeat?->created_at;
            $estadoConexion = match (true) {
                $ultimaFecha === null => 'sin_datos',
                $ultimaFecha->greaterThanOrEqualTo($desde) && $ultimaFecha->lessThanOrEqualTo($ahora) => 'conectado',
                default => 'desconectado',
            };

            $equipo->setAttribute('estado_conexion', $estadoConexion);
        });

        $ultimaActualizacionQuery = Heartbeat::query();
        if ($request->user()->isDocente()) {
            $ultimaActualizacionQuery->whereIn(
                'equipo_id',
                Asignacion::forTeacher($request->user())->select('equipo_id')->distinct()
            );
        }

        $ultimaActualizacion = $ultimaActualizacionQuery->max('created_at');
        $ultimaActualizacion = $ultimaActualizacion === null ? null : Carbon::parse($ultimaActualizacion);

        return view('monitoreo.index', compact(
            'equipos',
            'estadisticas',
            'filtros',
            'ultimaActualizacion',
        ));
    }
}

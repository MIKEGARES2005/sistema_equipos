<?php
namespace App\Http\Controllers;
use App\Models\Asignacion;
use App\Models\Equipo;
use App\Models\Incidencia;

use Illuminate\Http\Request;
class IncidenciaController extends Controller
{
    public function index()
    {
        $incidencias = Incidencia::with([
            'equipo',
            'asignacion.estudiante',
        ])->latest()->get();
        return view('incidencias.index', compact('incidencias'));
    }
    public function create()
    {
        $equipos = Equipo::orderBy('codigo')->get();
        $asignaciones = Asignacion::with([
            'equipo',
            'estudiante',
        ])
        ->where('estado', 'activa')
        ->latest()
        ->get();
        return view('incidencias.create', compact(
            'equipos',
            'asignaciones'
        ));
    }
    public function store(Request $request)
    {
        $datos = $request->validate([
            'equipo_id' => ['required', 'exists:equipos,id'],
            'asignacion_id' => ['nullable', 'exists:asignaciones,id'],
            'tipo' => ['required', 'string', 'max:50'],
            'descripcion' => ['required', 'string'],
            'prioridad' => ['required', 'string', 'max:20'],
            'estado' => ['required', 'string', 'max:30'],
            'fecha_reporte' => ['required', 'date'],
            'fecha_resolucion' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string'],
        ]);
        Incidencia::create($datos);
        return redirect()
            ->route('incidencias.index')
            ->with('success', 'Incidencia registrada correctamente.');
    }
    public function resolver($id)
    {
        $incidencia = Incidencia::findOrFail($id);
        if ($incidencia->estado === 'Resuelta') {
            return back()->withErrors([
                'incidencia' => 'La incidencia ya está resuelta.'
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
    public function destroy($id)
    {
        $incidencia = Incidencia::findOrFail($id);
        $incidencia->delete();
        return redirect()
            ->route('incidencias.index')
            ->with('success', 'Incidencia eliminada correctamente.');
    }
}
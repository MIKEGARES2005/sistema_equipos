<?php
namespace App\Http\Controllers;
use App\Models\Asignacion;
use App\Models\Equipo;
use App\Models\Estudiante;
use App\Models\Materia;
use Illuminate\Http\Request;
class AsignacionController extends Controller
{
    public function index()
    {
        $asignaciones = Asignacion::with([
            'equipo',
            'estudiante',
            'materia'
        ])->latest()->get();
    return view('asignaciones.index', compact('asignaciones'));
    }
    public function create()
    {
        $equipos = Equipo::where(function ($query) {
            $query->where('estado', 'Disponible')
                ->orWhere('modo_uso', 'grupal');
            })->get();
        $estudiantes = Estudiante::where('estado', 'activo')->get();
        $materias = Materia::where('estado', 'activo')->get();
        return view('asignaciones.create', compact(
            'equipos',
            'estudiantes',
            'materias'
        ));
    }
    public function store(Request $request)
    {
        $datos = $request->validate([
            'equipo_id' => ['required', 'exists:equipos,id'],
            'estudiante_id' => ['required', 'exists:estudiantes,id'],
            'materia_id' => ['required', 'exists:materias,id'],
            'grupo' => ['nullable', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'observaciones' => ['nullable', 'string'],
        ]);
        $equipo = Equipo::findOrFail($datos['equipo_id']);
    // Los equipos individuales deben estar disponibles.
    // Los equipos grupales pueden recibir nuevas asignaciones
    // aunque actualmente estén en uso.
        if (
            $equipo->modo_uso === 'individual' &&
            $equipo->estado !== 'Disponible'
        ) {
        return back()
                ->withInput()
                ->withErrors([
                'equipo_id' => 'El equipo seleccionado no está disponible.',
            ]);
        }
    // Si el equipo es de uso individual,
    // solamente puede tener una asignación activa.
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
            $datos['origen'] = 'panel_web';
            $datos['fecha_solicitud'] = now();
            $datos['fecha_aprobacion'] = now();
            $datos['estado'] = 'activa';
        $asignacion = Asignacion::create($datos);
        $equipo->update([
            'estado' => 'En uso',
        ]);
        return redirect()
            ->route('asignaciones.index')
            ->with('success', 'Asignación creada correctamente.');
    }
    public function finalizar($id)
    {
    $asignacion = Asignacion::findOrFail($id);
    if ($asignacion->estado !== 'activa') {
        return back()->withErrors([
            'asignacion' => 'La asignación seleccionada no está activa.'
        ]);
    }
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
    return redirect()
        ->route('asignaciones.index')
        ->with('success', 'Asignación finalizada correctamente.');
    }
    public function show(string $id)
    {
        //
    }
    public function edit(string $id)
    {
        //
    }
    public function update(Request $request, string $id)
    {
        //
    }
    public function destroy(string $id)
    {
        //
    }
}
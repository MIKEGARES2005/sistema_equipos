<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EquipoController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim($request->string('buscar')->toString());
        $equipos = Equipo::query()
            ->when($busqueda !== '', function ($query) use ($busqueda): void {
                $query->where(function ($equipoQuery) use ($busqueda): void {
                    $equipoQuery->where('codigo', 'like', "%{$busqueda}%")
                        ->orWhere('tipo_equipo', 'like', "%{$busqueda}%")
                        ->orWhere('marca', 'like', "%{$busqueda}%")
                        ->orWhere('modelo', 'like', "%{$busqueda}%")
                        ->orWhere('numero_serie', 'like', "%{$busqueda}%")
                        ->orWhere('estado', 'like', "%{$busqueda}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('equipos.index', compact('equipos'));
    }

    public function create(): View
    {
        return view('equipos.create', ['equipo' => new Equipo]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validatedData($request);

        if ($request->hasFile('imagen_referencial')) {
            $datos['imagen_referencial'] = $request->file('imagen_referencial')->store('equipos', 'public');
        }

        Equipo::create($datos);

        return redirect()
            ->route('equipos.index')
            ->with('success', 'Equipo registrado correctamente.');
    }

    public function show(int $id): View
    {
        $equipo = Equipo::with('accesorios')->findOrFail($id);

        return view('equipos.show', compact('equipo'));
    }

    public function edit(int $id): View
    {
        $equipo = Equipo::findOrFail($id);

        return view('equipos.edit', compact('equipo'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $equipo = Equipo::findOrFail($id);
        $datos = $this->validatedData($request, $equipo);
        $imagenAnterior = $equipo->imagen_referencial;

        if ($request->hasFile('imagen_referencial')) {
            $datos['imagen_referencial'] = $request->file('imagen_referencial')->store('equipos', 'public');
        }

        $equipo->update($datos);

        if (isset($datos['imagen_referencial']) && $imagenAnterior) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        return redirect()
            ->route('equipos.show', $equipo->id)
            ->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $equipo = Equipo::findOrFail($id);

        if ($equipo->asignaciones()->exists()
            || $equipo->incidencias()->exists()
            || $equipo->accesorios()->exists()
            || $equipo->heartbeats()->exists()
            || $equipo->diagnosticos()->exists()) {
            return back()->withErrors([
                'equipo' => 'No se puede eliminar este equipo porque tiene préstamos, incidencias, accesorios o registros de diagnóstico y telemetría asociados.',
            ]);
        }

        if ($equipo->imagen_referencial) {
            Storage::disk('public')->delete($equipo->imagen_referencial);
        }

        $equipo->delete();

        return redirect()
            ->route('equipos.index')
            ->with('success', 'Equipo eliminado correctamente.');
    }

    private function validatedData(Request $request, ?Equipo $equipo = null): array
    {
        return $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('equipos', 'codigo')->ignore($equipo?->id)],
            'tipo_equipo' => ['required', 'string', 'max:30'],
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:100'],
            'numero_serie' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('equipos', 'numero_serie')->ignore($equipo?->id),
            ],
            'modo_uso' => ['required', Rule::in(['individual', 'grupal'])],
            'estado' => ['required', Rule::in(['Disponible', 'En uso', 'Mantenimiento', 'Fuera de servicio'])],
            'mac' => ['nullable', 'string', 'max:17'],
            'imagen_referencial' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }
}

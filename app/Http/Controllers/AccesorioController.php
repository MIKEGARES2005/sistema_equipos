<?php

namespace App\Http\Controllers;

use App\Models\Accesorio;
use App\Models\Equipo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AccesorioController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim($request->string('buscar')->toString());
        $accesorios = Accesorio::with('equipo')
            ->when($busqueda !== '', function ($query) use ($busqueda): void {
                $query->where(function ($accesorioQuery) use ($busqueda): void {
                    $accesorioQuery->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('marca', 'like', "%{$busqueda}%")
                        ->orWhere('modelo', 'like', "%{$busqueda}%")
                        ->orWhere('numero_serie', 'like', "%{$busqueda}%")
                        ->orWhere('estado', 'like', "%{$busqueda}%")
                        ->orWhereHas('equipo', function ($equipoQuery) use ($busqueda): void {
                            $equipoQuery->where('codigo', 'like', "%{$busqueda}%")
                                ->orWhere('marca', 'like', "%{$busqueda}%")
                                ->orWhere('modelo', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('accesorios.index', compact('accesorios'));
    }

    public function create(): View
    {
        $equipos = Equipo::orderBy('codigo')->get();

        return view('accesorios.create', [
            'accesorio' => new Accesorio(['cantidad' => 1, 'estado' => 'Disponible']),
            'equipos' => $equipos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validatedData($request);

        if ($request->hasFile('imagen_referencial')) {
            $datos['imagen_referencial'] = $request->file('imagen_referencial')->store('accesorios', 'public');
        }

        Accesorio::create($datos);

        return redirect()
            ->route('accesorios.index')
            ->with('success', 'Accesorio registrado correctamente.');
    }

    public function show(int $id): View
    {
        $accesorio = Accesorio::with('equipo')->findOrFail($id);

        return view('accesorios.show', compact('accesorio'));
    }

    public function edit(int $id): View
    {
        return view('accesorios.edit', [
            'accesorio' => Accesorio::findOrFail($id),
            'equipos' => Equipo::orderBy('codigo')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $accesorio = Accesorio::findOrFail($id);
        $datos = $this->validatedData($request, $accesorio);
        $imagenAnterior = $accesorio->imagen_referencial;

        if ($request->hasFile('imagen_referencial')) {
            $datos['imagen_referencial'] = $request->file('imagen_referencial')->store('accesorios', 'public');
        }

        $accesorio->update($datos);

        if (isset($datos['imagen_referencial']) && $imagenAnterior) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        return redirect()
            ->route('accesorios.show', $accesorio->id)
            ->with('success', 'Accesorio actualizado correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $accesorio = Accesorio::findOrFail($id);

        if ($accesorio->imagen_referencial) {
            Storage::disk('public')->delete($accesorio->imagen_referencial);
        }

        $accesorio->delete();

        return redirect()
            ->route('accesorios.index')
            ->with('success', 'Accesorio eliminado correctamente.');
    }

    private function validatedData(Request $request, ?Accesorio $accesorio = null): array
    {
        return $request->validate([
            'equipo_id' => ['required', 'exists:equipos,id'],
            'nombre' => ['required', 'string', 'max:50'],
            'marca' => ['nullable', 'string', 'max:50'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'numero_serie' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('accesorios', 'numero_serie')->ignore($accesorio?->id),
            ],
            'cantidad' => ['required', 'integer', 'min:1'],
            'estado' => ['required', Rule::in(['Disponible', 'En uso', 'Dañado', 'Mantenimiento'])],
            'observaciones' => ['nullable', 'string'],
            'imagen_referencial' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }
}

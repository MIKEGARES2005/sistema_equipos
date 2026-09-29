<?php
namespace App\Http\Controllers;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class EquipoController extends Controller
{
    public function index()
    {
            $equipos = Equipo::latest()->get();
        return view('equipos.index', compact('equipos'));
    }
    public function create()
    {
        return view('equipos.create');
    }
    public function store(Request $request)
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', 'unique:equipos,codigo'],
            'tipo_equipo' => ['required', 'string', 'max:30'],
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100', 'unique:equipos,numero_serie'],
            'modo_uso' => ['required', 'string', 'max:20'],
            'estado' => ['required', 'string', 'max:30'],
            'mac' => ['nullable', 'string', 'max:17'],
            'imagen_referencial' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        if ($request->hasFile('imagen_referencial')) {
            $datos['imagen_referencial'] = $request->file('imagen_referencial')->store('equipos', 'public');
        }
        Equipo::create($datos);
        return redirect()->route('equipos.index');
    }
    public function show(string $id)
    {
        $equipo = Equipo::findOrFail($id);
        return view('equipos.show', compact('equipo'));
    }
    public function edit(string $id)
    {
        $equipo = Equipo::findOrFail($id);
        return view('equipos.edit', compact('equipo'));
    }
    public function update(Request $request, string $id)
    {
    $equipo = Equipo::findOrFail($id);
    $datos = $request->validate([
        'codigo' => ['required', 'string', 'max:20', 'unique:equipos,codigo,' . $equipo->id],
        'tipo_equipo' => ['required', 'string', 'max:30'],
        'marca' => ['required', 'string', 'max:50'],
        'modelo' => ['required', 'string', 'max:100'],
        'numero_serie' => ['nullable', 'string', 'max:100', 'unique:equipos,numero_serie,' . $equipo->id],
        'modo_uso' => ['required', 'string', 'max:20'],
        'estado' => ['required', 'string', 'max:30'],
        'mac' => ['nullable', 'string', 'max:17'],
        'imagen_referencial' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ]);
    if ($request->hasFile('imagen_referencial')) {
    if ($equipo->imagen_referencial) {
        Storage::disk('public')->delete($equipo->imagen_referencial);
    }
    $datos['imagen_referencial'] = $request->file('imagen_referencial')->store('equipos', 'public');
    }
    $equipo->update($datos);
    return redirect()->route('equipos.show', $equipo->id);
    }
    public function destroy(string $id)
    {
        $equipo = Equipo::findOrFail($id);
            if ($equipo->imagen_referencial) {
            Storage::disk('public')->delete($equipo->imagen_referencial);
            }
        $equipo->delete();
    return redirect()->route('equipos.index');
    }
}
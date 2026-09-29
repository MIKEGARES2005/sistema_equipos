<?php
namespace App\Http\Controllers;
use App\Models\Accesorio;
use App\Models\Equipo;
use Illuminate\Http\Request;
class AccesorioController extends Controller
{
    public function index()
    {
        $accesorios = Accesorio::with('equipo')
            ->latest()
            ->get();
        return view('accesorios.index', compact('accesorios'));
    }
    public function create()
    {
        $equipos = Equipo::orderBy('codigo')->get();
        return view('accesorios.create', compact('equipos'));
    }
    public function store(Request $request)
    {
        $datos = $request->validate([
            'equipo_id' => ['required', 'exists:equipos,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'marca' => ['nullable', 'string', 'max:50'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'string', 'max:30'],
            'observaciones' => ['nullable', 'string'],
        ]);
        Accesorio::create($datos);
        return redirect()
            ->route('accesorios.index')
            ->with('success', 'Accesorio registrado correctamente.');
    }
    public function destroy($id)
    {
        $accesorio = Accesorio::findOrFail($id);
        $accesorio->delete();
        return redirect()
            ->route('accesorios.index')
            ->with('success', 'Accesorio eliminado correctamente.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Invitado;
use App\Services\BitacoraService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvitadoController extends Controller
{
    /** Mostrar y filtrar invitados activos. */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $estado = $request->query('estado', 'todos');

        if (! in_array($estado, ['todos', 'activo', 'inactivo'], true)) {
            $estado = 'todos';
        }

        $invitados = Invitado::query()
            ->when($buscar !== '', function ($query) use ($buscar): void {
                $like = '%'.mb_strtolower($buscar).'%';

                $query->where(function ($consulta) use ($like): void {
                    $consulta->whereRaw('LOWER(nombres) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(apellido_paterno) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(apellido_materno) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(ci) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(procedencia) LIKE ?', [$like]);
                });
            })
            ->when($estado !== 'todos', fn ($query) => $query->where('estado', $estado))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('invitados.index', compact('invitados', 'buscar', 'estado'));
    }

    /** Mostrar el formulario de creación. */
    public function create(): View
    {
        return view('invitados.create');
    }

    /** Guardar un invitado. */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->rules());
        $invitado = Invitado::create($datos);

        BitacoraService::registrar(
            'crear_invitado',
            'invitados',
            $invitado->id,
            null,
            $invitado->toArray()
        );

        return redirect()->route('invitados.index')
            ->with('success', 'El invitado se registró correctamente.');
    }

    /** Mostrar el detalle de un invitado. */
    public function show(Invitado $invitado): View
    {
        return view('invitados.show', compact('invitado'));
    }

    /** Mostrar el formulario de edición. */
    public function edit(Invitado $invitado): View
    {
        return view('invitados.edit', compact('invitado'));
    }

    /** Actualizar un invitado. */
    public function update(Request $request, Invitado $invitado): RedirectResponse
    {
        $datos = $request->validate($this->rules($invitado));
        $antes = $invitado->toArray();
        $invitado->update($datos);

        BitacoraService::registrar(
            'actualizar_invitado',
            'invitados',
            $invitado->id,
            $antes,
            $invitado->fresh()->toArray()
        );

        return redirect()->route('invitados.show', $invitado)
            ->with('success', 'Los datos del invitado se actualizaron correctamente.');
    }

    /** Eliminar lógicamente un invitado. */
    public function destroy(Invitado $invitado): RedirectResponse
    {
        $antes = $invitado->toArray();
        $invitado->delete();

        BitacoraService::registrar(
            'eliminar_invitado',
            'invitados',
            $invitado->id,
            $antes,
            ['deleted_at' => now()->toDateTimeString()]
        );

        return redirect()->route('invitados.index')
            ->with('success', 'El invitado fue archivado correctamente.');
    }

    /** @return array<string, array<int, mixed>|string> */
    private function rules(?Invitado $invitado = null): array
    {
        return [
            'tipo' => ['required', Rule::in(['estudiante', 'docente', 'externo'])],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'ci' => [
                'required',
                'string',
                'max:20',
                Rule::unique('invitados', 'ci')->ignore($invitado?->id),
            ],
            'ci_extension' => ['nullable', 'string', 'max:2'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'semestre' => ['nullable', 'integer', 'between:1,4'],
            'procedencia' => ['nullable', 'string', 'max:150'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
        ];
    }
}

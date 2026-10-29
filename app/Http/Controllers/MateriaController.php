<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MateriaController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('gestion-academica.index');
    }

    public function create(Request $request): View
    {
        $semestre = $request->query('semestre');

        return view('academica.materias.form', [
            'materia' => new Materia([
                'estado' => 'activo',
                'semestre' => $semestre,
            ]),
            'semestres' => Materia::SEMESTRES,
            'gestion' => $request->query('gestion'),
            'semestreSeleccionado' => $semestre,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'sigla' => ['required', 'string', 'max:20', Rule::unique('materias', 'sigla')],
            'semestre' => ['required', Rule::in(Materia::SEMESTRES)],
        ]);

        Materia::create($datos + ['estado' => 'activo']);

        return redirect()
            ->route('gestion-academica.semestre', [
                'semestre' => $datos['semestre'],
                'gestion' => $request->input('gestion'),
            ])
            ->with('success', 'Materia creada correctamente.');
    }

    public function edit(Materia $materia): View
    {
        return view('academica.materias.form', [
            'materia' => $materia,
            'semestres' => Materia::SEMESTRES,
            'gestion' => request()->query('gestion'),
            'semestreSeleccionado' => $materia->semestre,
        ]);
    }

    public function update(Request $request, Materia $materia): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'sigla' => [
                'required',
                'string',
                'max:20',
                Rule::unique('materias', 'sigla')->ignore($materia->id),
            ],
            'semestre' => ['required', Rule::in(Materia::SEMESTRES)],
        ]);

        $materia->update($datos);

        return redirect()
            ->route('gestion-academica.semestre', [
                'semestre' => $materia->semestre,
                'gestion' => $request->input('gestion'),
            ])
            ->with('success', 'Materia actualizada correctamente.');
    }

    public function updateStatus(Request $request, Materia $materia): RedirectResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
        ]);

        $materia->update($datos);

        return redirect()
            ->route('gestion-academica.semestre', [
                'semestre' => $materia->semestre,
                'gestion' => $request->input('gestion'),
            ])
            ->with('success', 'Estado de la materia actualizado correctamente.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionAcademica;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\Materia;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HorarioController extends Controller
{
    private const DIAS_SEMANA = [
        'Lunes',
        'Martes',
        'Miercoles',
        'Jueves',
        'Viernes',
    ];

    public function index(): RedirectResponse
    {
        return redirect()->route('gestion-academica.index');
    }

    public function create(Request $request): View
    {
        $semestre = $request->query('semestre');
        $horario = new Horario([
            'estado' => 'activo',
            'materia_id' => $request->query('materia_id'),
        ]);

        return view('academica.horarios.form', $this->formData($horario, $semestre, $request->query('gestion')));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validatedData($request);
        $datos['gestion'] ??= ConfiguracionAcademica::gestionVigente();
        $horario = Horario::create($datos + ['estado' => 'activo']);

        return redirect()
            ->route('gestion-academica.semestre', [
                'semestre' => $horario->materia->semestre,
                'gestion' => $request->input('gestion'),
            ])
            ->with('success', 'Horario creado correctamente.');
    }

    public function edit(Request $request, Horario $horario): View
    {
        return view('academica.horarios.form', $this->formData(
            $horario,
            $horario->materia->semestre,
            $request->query('gestion'),
        ));
    }

    public function update(Request $request, Horario $horario): RedirectResponse
    {
        $datos = $this->validatedData($request);
        $datos['gestion'] ??= $horario->gestion ?? ConfiguracionAcademica::gestionVigente();
        $horario->update($datos);

        return redirect()
            ->route('gestion-academica.semestre', [
                'semestre' => $horario->materia->semestre,
                'gestion' => $request->input('gestion'),
            ])
            ->with('success', 'Horario actualizado correctamente.');
    }

    public function updateStatus(Request $request, Horario $horario): RedirectResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
        ]);

        $horario->update($datos);

        return redirect()
            ->route('gestion-academica.semestre', [
                'semestre' => $horario->materia->semestre,
                'gestion' => $request->input('gestion'),
            ])
            ->with('success', 'Estado del horario actualizado correctamente.');
    }

    private function formData(Horario $horario, ?string $semestre, ?string $gestion): array
    {
        $materias = Materia::query()
            ->orderBy('semestre')
            ->orderBy('nombre');

        if ($semestre !== null) {
            $materias->where('semestre', $semestre);
        }

        return [
            'horario' => $horario,
            'semestre' => $semestre,
            'gestion' => $gestion,
            'docentes' => Docente::query()
                ->orderBy('apellido_paterno')
                ->orderBy('nombres')
                ->get(),
            'materias' => $materias->get(),
            'diasSemana' => self::DIAS_SEMANA,
        ];
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'docente_id' => ['required', 'exists:docentes,id'],
            'materia_id' => ['required', 'exists:materias,id'],
            'dia_semana' => ['required', Rule::in(self::DIAS_SEMANA)],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'aula' => ['nullable', 'string', 'max:100'],
            'gestion' => ['nullable', 'string', 'regex:/^\d{4}-(I|II)$/'],
        ]);
    }
}

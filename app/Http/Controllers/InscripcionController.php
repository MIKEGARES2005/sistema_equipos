<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionAcademica;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Models\User;
use App\Services\BitacoraService;
use App\Services\NotificacionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InscripcionController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('gestion-academica.index');
    }

    public function create(Request $request): View
    {
        $semestre = $request->query('semestre');
        abort_unless($semestre === null || in_array($semestre, Materia::SEMESTRES, true), 404);
        $gestion = $request->query('gestion', ConfiguracionAcademica::gestionVigente());

        return view('academica.inscripciones.form', $this->formData(new Inscripcion([
            'estado' => 'Cursando',
            'fecha_inscripcion' => now()->toDateString(),
            'gestion' => $gestion,
            'materia_id' => $request->query('materia_id'),
        ]), $semestre));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validatedData($request);
        $this->ensureStudentIsInFollowUp($datos['estudiante_id']);
        Inscripcion::create($datos + ['estado' => 'Cursando']);

        return $this->redirectToSemester($datos['materia_id'], $datos['gestion'])
            ->with('success', 'Inscripción registrada correctamente.');
    }

    public function edit(Inscripcion $inscripcion): View
    {
        return view(
            'academica.inscripciones.form',
            $this->formData($inscripcion, $inscripcion->materia->semestre),
        );
    }

    public function update(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $datos = $this->validatedData($request, $inscripcion);
        $this->ensureStudentIsInFollowUp($datos['estudiante_id']);

        if (in_array($datos['estado'], ['Aprobado', 'Reprobado'], true)
            && $inscripcion->resultado_reportado === null) {
            throw ValidationException::withMessages([
                'estado' => 'El docente debe reportar el resultado antes de la revisión administrativa.',
            ]);
        }

        if ($datos['estado'] !== $inscripcion->estado) {
            $datos['revisado_por'] = $request->user()->id;
            $datos['fecha_revision'] = now();
        }

        $datos['resultado_final'] = in_array($datos['estado'], ['Aprobado', 'Reprobado'], true)
            ? $datos['estado']
            : null;

        $estadoAnterior = $inscripcion->estado;
        $antes = $inscripcion->only(['estado', 'resultado_final', 'revisado_por', 'fecha_revision']);

        $inscripcion->update($datos);

        BitacoraService::registrar(
            'actualizar_inscripcion',
            'inscripciones',
            $inscripcion->id,
            $antes,
            $inscripcion->only(['estado', 'resultado_final', 'revisado_por', 'fecha_revision']),
            $inscripcion->materia_id,
        );

        if ($estadoAnterior !== $inscripcion->estado
            && in_array($inscripcion->estado, ['Aprobado', 'Reprobado'], true)) {
            $this->notificarDecisionDocente($inscripcion);
        }

        return $this->redirectToSemester($datos['materia_id'], $datos['gestion'])
            ->with('success', 'Inscripción actualizada correctamente.');
    }

    public function updateStatus(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(['Cursando', 'Aprobado', 'Reprobado', 'Inactivo'])],
        ]);
        $this->ensureStudentIsInFollowUp($inscripcion->estudiante_id, 'estado');

        if (in_array($datos['estado'], ['Aprobado', 'Reprobado'], true)
            && $inscripcion->resultado_reportado === null) {
            return back()->withErrors([
                'estado' => 'El docente debe reportar el resultado antes de la revisión administrativa.',
            ]);
        }

        $estadoAnterior = $inscripcion->estado;
        $antes = $inscripcion->only(['estado', 'resultado_final', 'revisado_por', 'fecha_revision']);

        $inscripcion->update([
            'estado' => $datos['estado'],
            'resultado_final' => in_array($datos['estado'], ['Aprobado', 'Reprobado'], true)
                ? $datos['estado']
                : null,
            'revisado_por' => $request->user()->id,
            'fecha_revision' => now(),
        ]);

        BitacoraService::registrar(
            'revisar_inscripcion',
            'inscripciones',
            $inscripcion->id,
            $antes,
            $inscripcion->only(['estado', 'resultado_final', 'revisado_por', 'fecha_revision']),
            $inscripcion->materia_id,
        );

        if ($estadoAnterior !== $inscripcion->estado
            && in_array($inscripcion->estado, ['Aprobado', 'Reprobado'], true)) {
            $this->notificarDecisionDocente($inscripcion);
        }

        return $this->redirectToSemester($inscripcion->materia_id, $inscripcion->gestion)
            ->with('success', 'Decisión académica registrada correctamente.');
    }

    public function registerPartial(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $datos = $request->validate([
            'parcial' => ['required', Rule::in(['1', '2'])],
            'nota' => ['required', 'numeric', 'between:0,100'],
        ]);

        $asignacion = $this->assignedEnrollment($request, $inscripcion);
        $this->ensureStudentIsInFollowUp($asignacion->estudiante_id, 'nota');

        if ($asignacion->estado !== 'Cursando') {
            return back()->withErrors([
                'nota' => 'Solo se pueden registrar notas de inscripciones que estén cursando.',
            ]);
        }

        $numero = (int) $datos['parcial'];

        if ($numero === 2 && $asignacion->parcial_1 === null) {
            return back()->withErrors([
                'nota' => 'Primero debes registrar la nota del primer parcial.',
            ]);
        }

        $antes = $asignacion->only([
            'parcial_1',
            'parcial_1_por',
            'parcial_1_en',
            'parcial_2',
            'parcial_2_por',
            'parcial_2_en',
            'nota_final',
            'nota_final_en',
        ]);

        $atributos = $numero === 1
            ? [
                'parcial_1' => (float) $datos['nota'],
                'parcial_1_por' => $request->user()->id,
                'parcial_1_en' => now(),
            ]
            : [
                'parcial_2' => (float) $datos['nota'],
                'parcial_2_por' => $request->user()->id,
                'parcial_2_en' => now(),
            ];

        $asignacion->fill($atributos);

        if ($asignacion->tieneAmbosParciales()) {
            $asignacion->nota_final = $asignacion->calcularNotaFinal();
            $asignacion->nota_final_en = now();
        }

        $asignacion->save();

        BitacoraService::registrar(
            "registrar_parcial_{$numero}",
            'inscripciones',
            $asignacion->id,
            $antes,
            $asignacion->only(array_keys($antes)),
            $asignacion->materia_id,
        );

        NotificacionService::notificarAdmins(
            'calificacion',
            "Parcial {$numero} registrado",
            "Se registró el parcial {$numero} de {$asignacion->estudiante?->nombres} {$asignacion->estudiante?->apellido_paterno} en {$asignacion->materia?->nombre}.",
            route('gestion-academica.semestre', [
                'semestre' => $asignacion->materia?->semestre,
                'gestion' => $asignacion->gestion,
            ]),
        );

        $mensaje = $asignacion->nota_final !== null
            ? 'Segundo parcial registrado. Nota final calculada: '.number_format((float) $asignacion->nota_final, 2).'.'
            : 'Primer parcial registrado correctamente.';

        return back()->with('success', $mensaje);
    }

    public function reportResult(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        $datos = $request->validate([
            'resultado_reportado' => ['required', Rule::in(['Aprobado', 'Reprobado'])],
        ]);
        $this->ensureStudentIsInFollowUp($inscripcion->estudiante_id, 'resultado_reportado');

        $asignacionDocente = $this->assignedEnrollment($request, $inscripcion);

        if ($asignacionDocente->estado !== 'Cursando') {
            return back()->withErrors([
                'resultado_reportado' => 'Solo se pueden reportar resultados de inscripciones que estén cursando.',
            ]);
        }

        if (! $asignacionDocente->tieneAmbosParciales()) {
            return back()->withErrors([
                'resultado_reportado' => 'Debes registrar el primer y el segundo parcial antes de reportar el resultado.',
            ]);
        }

        $notaFinal = $asignacionDocente->calcularNotaFinal();
        $resultadoEsperado = $asignacionDocente->resultadoSegunNotaFinal();

        if ($datos['resultado_reportado'] !== $resultadoEsperado) {
            return back()->withErrors([
                'resultado_reportado' => sprintf(
                    'La decisión debe coincidir con la nota final %.2f, que corresponde a %s.',
                    $notaFinal,
                    $resultadoEsperado,
                ),
            ]);
        }

        $antes = $asignacionDocente->only(['resultado_reportado', 'resultado_reportado_por', 'fecha_reporte']);

        $asignacionDocente->update([
            'resultado_reportado' => $datos['resultado_reportado'],
            'resultado_reportado_por' => $request->user()->id,
            'fecha_reporte' => now(),
        ]);

        BitacoraService::registrar(
            'reportar_resultado',
            'inscripciones',
            $asignacionDocente->id,
            $antes,
            $asignacionDocente->only(array_keys($antes)),
            $asignacionDocente->materia_id,
        );

        NotificacionService::notificarAdmins(
            'resultado_reportado',
            'Resultado académico reportado',
            "El docente reportó {$asignacionDocente->resultado_reportado} para {$asignacionDocente->estudiante?->nombres} {$asignacionDocente->estudiante?->apellido_paterno} en {$asignacionDocente->materia?->nombre}.",
            route('gestion-academica.semestre', [
                'semestre' => $asignacionDocente->materia?->semestre,
                'gestion' => $asignacionDocente->gestion,
            ]),
        );

        return back()->with('success', 'Resultado enviado al administrador para su revisión.');
    }

    private function assignedEnrollment(Request $request, Inscripcion $inscripcion): Inscripcion
    {
        return Inscripcion::query()
            ->whereKey($inscripcion->id)
            ->whereHas('materia.horarios', function (Builder $horarios) use ($request): void {
                $horarios->where('docente_id', $request->user()->docente_id);
            })
            ->firstOrFail();
    }

    private function notificarDecisionDocente(Inscripcion $inscripcion): void
    {
        $docenteId = $inscripcion->materia?->horarios()->value('docente_id');

        if ($docenteId === null) {
            return;
        }

        $docente = User::query()
            ->where('docente_id', $docenteId)
            ->where('tipo_usuario', 'docente')
            ->first();

        if ($docente === null) {
            return;
        }

        NotificacionService::notificarUsuario(
            $docente->id,
            'decision_academica',
            'Decisión académica registrada',
            "La inscripción de {$inscripcion->estudiante?->nombres} {$inscripcion->estudiante?->apellido_paterno} en {$inscripcion->materia?->nombre} fue marcada como {$inscripcion->estado}.",
            route('gestion-academica.semestre', [
                'semestre' => $inscripcion->materia?->semestre,
                'gestion' => $inscripcion->gestion,
            ]),
        );
    }

    private function formData(Inscripcion $inscripcion, ?string $semestre): array
    {
        $materias = Materia::query()->orderBy('semestre')->orderBy('nombre');

        if ($semestre !== null) {
            $materias->where('semestre', $semestre);
        }

        return [
            'inscripcion' => $inscripcion,
            'semestre' => $semestre,
            'estudiantes' => Estudiante::query()
                ->where('estado', 'activo')
                ->whereDoesntHave('historialSeguimiento')
                ->orderBy('semestre')
                ->orderBy('apellido_paterno')
                ->orderBy('nombres')
                ->get(),
            'materias' => $materias->get(),
        ];
    }

    private function validatedData(Request $request, ?Inscripcion $inscripcion = null): array
    {
        $estudianteId = $request->input('estudiante_id');
        $materiaId = $request->input('materia_id');
        $gestion = $request->input('gestion');

        $reglas = [
            'estudiante_id' => ['required', 'exists:estudiantes,id'],
            'materia_id' => [
                'required',
                'exists:materias,id',
                Rule::unique('inscripciones')
                    ->where(fn (QueryBuilder $query) => $query
                        ->where('estudiante_id', $estudianteId)
                        ->where('materia_id', $materiaId)
                        ->where('gestion', $gestion))
                    ->ignore($inscripcion?->id),
            ],
            'gestion' => ['required', 'regex:/^\d{4}-(I|II)$/'],
            'tipo' => ['required', Rule::in(['Normal', 'Arrastre'])],
            'fecha_inscripcion' => ['required', 'date'],
        ];

        if ($inscripcion !== null) {
            $reglas['estado'] = ['required', Rule::in(['Cursando', 'Aprobado', 'Reprobado', 'Inactivo'])];
        }

        return $request->validate($reglas, [
            'materia_id.unique' => 'El estudiante ya tiene una inscripción registrada en esta materia durante esta gestión.',
        ], [
            'gestion' => 'gestión académica',
        ]);
    }

    private function redirectToSemester(int $materiaId, string $gestion): RedirectResponse
    {
        $materia = Materia::findOrFail($materiaId);

        return redirect()->route('gestion-academica.semestre', [
            'semestre' => $materia->semestre,
            'gestion' => $gestion,
        ]);
    }

    private function ensureStudentIsInFollowUp(int $studentId, string $field = 'estudiante_id'): void
    {
        $finalized = Estudiante::query()
            ->whereKey($studentId)
            ->whereHas('historialSeguimiento')
            ->exists();

        if ($finalized) {
            throw ValidationException::withMessages([
                $field => 'Este estudiante ya finalizó su seguimiento en el sistema y sus registros académicos no pueden modificarse.',
            ]);
        }
    }
}

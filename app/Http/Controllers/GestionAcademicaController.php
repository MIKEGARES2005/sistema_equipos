<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionAcademica;
use App\Models\Estudiante;
use App\Models\Gestion;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Materia;
use App\Services\BitacoraService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GestionAcademicaController extends Controller
{
    // ==========================================
    // 1. GESTIONES ACADÉMICAS
    // ==========================================
    public function indexGestiones(Request $request): View
    {
        $gestion = ConfiguracionAcademica::gestionVigente();
        $user = $request->user();

        $semestres = collect(['Primero', 'Segundo', 'Tercero'])
            ->map(function (string $nombre) use ($user, $gestion): array {
                $materias = Materia::query()->where('semestre', $nombre);
                $horarios = Horario::query()
                    ->whereHas('materia', fn (Builder $query) => $query->where('semestre', $nombre));
                $inscripciones = Inscripcion::query()
                    ->where('gestion', $gestion)
                    ->whereHas('materia', fn (Builder $query) => $query->where('semestre', $nombre));

                if ($user?->isDocente() && ! $user->isAdministrador()) {
                    $materias->whereHas(
                        'horarios',
                        fn (Builder $query) => $query->where('docente_id', $user->docente_id),
                    );
                    $horarios->where('docente_id', $user->docente_id);
                    $inscripciones->whereHas(
                        'materia.horarios',
                        fn (Builder $query) => $query->where('docente_id', $user->docente_id),
                    );
                }

                return [
                    'nombre' => $nombre,
                    'materias' => (clone $materias)->count(),
                    'horarios' => $horarios->count(),
                    'inscripciones' => $inscripciones->count(),
                ];
            });

        return view('academica.index', compact('gestion', 'semestres'));
    }

    public function semestre(Request $request, string $semestre): View
    {
        abort_unless(in_array($semestre, Materia::SEMESTRES, true), 404);

        $user = $request->user();
        $gestionVigente = ConfiguracionAcademica::gestionVigente();
        $gestion = (string) $request->query('gestion', $gestionVigente);

        if (! preg_match('/^\d{4}-(I|II)$/', $gestion)) {
            $gestion = $gestionVigente;
        }

        $consulta = Materia::query()->where('semestre', $semestre);

        if ($user->isDocente() && ! $user->isAdministrador()) {
            $consulta->whereHas(
                'horarios',
                fn (Builder $query) => $query->where('docente_id', $user->docente_id),
            );
        }

        $materias = $consulta
            ->with([
                'horarios' => fn ($query) => $query
                    ->orderBy('dia_semana')
                    ->orderBy('hora_inicio'),
                'horarios.docente',
                'inscripciones' => fn ($query) => $query
                    ->where('gestion', $gestion)
                    ->with(['estudiante', 'reportadoPor', 'revisadoPor', 'primerParcialPor', 'segundoParcialPor'])
                    ->orderBy('estudiante_id'),
            ])
            ->orderBy('nombre')
            ->get();

        $gestiones = collect([$gestionVigente])
            ->merge(Inscripcion::query()->whereNotNull('gestion')->distinct()->pluck('gestion'))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $gestionesVigentes = ConfiguracionAcademica::query()
            ->pluck('gestion_vigente')
            ->merge(Inscripcion::query()->whereNotNull('gestion')->distinct()->pluck('gestion'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('academica.semestre', compact(
            'semestre',
            'gestion',
            'gestionVigente',
            'gestiones',
            'gestionesVigentes',
            'materias',
        ));
    }

    public function updateGestionVigente(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'gestion_vigente' => ['required', 'string', 'regex:/^\d{4}-(I|II)$/'],
            'semestre' => ['nullable', Rule::in(Materia::SEMESTRES)],
        ]);

        ConfiguracionAcademica::actualizarGestionVigente($datos['gestion_vigente']);

        return redirect()
            ->route('gestion-academica.semestre', [
                'semestre' => $datos['semestre'] ?? 'Primero',
            ])
            ->with('success', 'Gestión vigente actualizada para todos los semestres.');
    }

    public function storeGestion(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:7', 'unique:gestiones,codigo'],
            'anio' => ['required', 'integer', 'min:2024', 'max:2050'],
            'periodo' => ['required', Rule::in(['1', '2'])],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'activa' => ['boolean'],
        ]);

        DB::transaction(function () use ($datos) {
            if (! empty($datos['activa']) && $datos['activa']) {
                Gestion::query()->update(['activa' => false]);
            }

            $gestion = Gestion::create($datos);

            BitacoraService::registrar('crear_gestion', 'gestiones', $gestion->id, null, $gestion->toArray());
        });

        return redirect()->route('admin.gestiones.index')->with('success', 'Gestión académica creada con éxito.');
    }

    public function activarGestion(int $id): RedirectResponse
    {
        $gestion = Gestion::findOrFail($id);

        DB::transaction(function () use ($gestion) {
            Gestion::query()->update(['activa' => false]);
            $gestion->update(['activa' => true]);

            BitacoraService::registrar('activar_gestion', 'gestiones', $gestion->id, null, ['activa' => true]);
        });

        return back()->with('success', "La gestión {$gestion->codigo} ha sido marcada como activa.");
    }

    // ==========================================
    // 2. MATERIAS
    // ==========================================
    public function indexMaterias(Request $request): View
    {
        $buscar = $request->input('buscar');
        $materias = Materia::query()
            ->when($buscar, fn ($q) => $q->where('nombre', 'like', "%{$buscar}%")->orWhere('sigla', 'like', "%{$buscar}%"))
            ->orderBy('semestre')
            ->orderBy('nombre')
            ->paginate(15);

        return view('admin. academic.materias.index', compact('materias', 'buscar'));
    }

    public function storeMateria(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'sigla' => ['required', 'string', 'max:20', 'unique:materias,sigla'],
            'semestre' => ['required', 'integer', 'between:1,4'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
        ]);

        $materia = Materia::create($datos);
        BitacoraService::registrar('crear_materia', 'materias', $materia->id, null, $materia->toArray());

        return redirect()->route('admin.materias.index')->with('success', 'Materia registrada correctamente.');
    }

    // ==========================================
    // 3. HORARIOS
    // ==========================================
    public function indexHorarios(Request $request): View
    {
        $gestionActiva = Gestion::activa();
        $horarios = Horario::with(['gestionAcademica', 'docente', 'materia'])
            ->when($gestionActiva, fn ($q) => $q->where('gestion_id', $gestionActiva->id))
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->paginate(15);

        return view('admin. academic.horarios.index', compact('horarios', 'gestionActiva'));
    }

    public function storeHorario(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'gestion_id' => ['required', 'exists:gestiones,id'],
            'docente_id' => ['required', 'exists:docentes,id'],
            'materia_id' => ['required', 'exists:materias,id'],
            'dia_semana' => ['required', 'integer', 'between:1,7'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'aula' => ['nullable', 'string', 'max:100'],
        ]);

        // Validar cruce de docente en el mismo día y horario
        $cruceDocente = Horario::query()
            ->where('gestion_id', $datos['gestion_id'])
            ->where('docente_id', $datos['docente_id'])
            ->where('dia_semana', $datos['dia_semana'])
            ->where('estado', 'activo')
            ->where(function ($q) use ($datos) {
                $q->whereBetween('hora_inicio', [$datos['hora_inicio'], $datos['hora_fin']])
                    ->orWhereBetween('hora_fin', [$datos['hora_inicio'], $datos['hora_fin']])
                    ->orWhere(fn ($sub) => $sub->where('hora_inicio', '<=', $datos['hora_inicio'])->where('hora_fin', '>=', $datos['hora_fin']));
            })
            ->exists();

        if ($cruceDocente) {
            throw ValidationException::withMessages([
                'docente_id' => 'El docente ya cuenta con otro horario asignado que se solapa en este bloque de tiempo.',
            ]);
        }

        $horario = Horario::create($datos + ['estado' => 'activo']);
        BitacoraService::registrar('crear_horario', 'horarios', $horario->id, null, $horario->toArray());

        return redirect()->route('admin.horarios.index')->with('success', 'Horario programado con éxito.');
    }

    // ==========================================
    // 4. INSCRIPCIONES
    // ==========================================
    public function indexInscripciones(Request $request): View
    {
        $gestionActiva = Gestion::activa();
        $inscripciones = Inscripcion::with(['estudiante', 'materia', 'gestionAcademica'])
            ->when($gestionActiva, fn ($q) => $q->where('gestion_id', $gestionActiva->id))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin. academic.inscripciones.index', compact('inscripciones', 'gestionActiva'));
    }

    public function storeInscripcion(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'estudiante_id' => ['required', 'exists:estudiantes,id'],
            'materia_id' => ['required', 'exists:materias,id'],
            'gestion_id' => ['required', 'exists:gestiones,id'],
            'tipo' => ['required', Rule::in(['normal', 'arrastre'])],
        ]);

        // Verificar unicidad por estudiante, materia y gestión
        $existe = Inscripcion::query()
            ->where('estudiante_id', $datos['estudiante_id'])
            ->where('materia_id', $datos['materia_id'])
            ->where('gestion_id', $datos['gestion_id'])
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'materia_id' => 'El estudiante ya se encuentra inscrito en esta materia para la presente gestión.',
            ]);
        }

        $inscripcion = Inscripcion::create([
            ...$datos,
            'estado' => 'confirmada',
            'fecha_inscripcion' => now(),
        ]);

        BitacoraService::registrar('inscribir_estudiante', 'inscripciones', $inscripcion->id, null, $inscripcion->toArray());

        return redirect()->route('admin.inscripciones.index')->with('success', 'Inscripción procesada correctamente.');
    }
}

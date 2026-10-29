<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SecurityQuestionController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $this->authenticatedUser($request);
        $preguntas = $user->preguntasSeguridad()
            ->orderBy('id')
            ->get();

        return view('security.questions', [
            'preguntas' => $preguntas,
            'user' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $guard = $this->isStudentRoute($request) ? 'student' : 'web';
        $datos = $request->validate([
            'current_password' => ['required', 'current_password:'.$guard],
            'preguntas' => ['required', 'array', 'size:3'],
            'preguntas.*.pregunta' => ['required', 'string', 'max:255', 'distinct'],
            'preguntas.*.respuesta' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($datos, $user): void {
            $preguntasSeguridad = $user->preguntasSeguridad();
            $preguntasSeguridad->delete();

            foreach ($datos['preguntas'] as $pregunta) {
                $preguntasSeguridad->create([
                    'pregunta' => $this->normalizarPregunta($pregunta['pregunta']),
                    'respuesta_hash' => Hash::make($this->normalizarRespuesta($pregunta['respuesta'])),
                ]);
            }
        });

        BitacoraService::registrar(
            'actualizar_preguntas_seguridad',
            'users',
            $user->id
        );

        return back()->with('status', 'Preguntas de seguridad actualizadas correctamente.');
    }

    private function normalizarRespuesta(string $respuesta): string
    {
        return Str::ascii(Str::lower(trim($respuesta)));
    }

    private function normalizarPregunta(string $pregunta): string
    {
        $pregunta = trim((string) preg_replace('/^[¿]+|[?]+$/u', '', trim($pregunta)));

        return $pregunta === '' ? '' : '¿'.$pregunta.'?';
    }

    private function authenticatedUser(Request $request): User
    {
        return $this->isStudentRoute($request)
            ? $request->user('student')
            : $request->user();
    }

    private function isStudentRoute(Request $request): bool
    {
        return $request->routeIs('estudiante.*');
    }
}

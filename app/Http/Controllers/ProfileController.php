<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $this->authenticatedUser($request);

        return view('profile.edit', [
            'user' => $user->load(['docente', 'estudiante']),
        ]);
    }

    public function photo(Request $request): BinaryFileResponse
    {
        $user = $this->authenticatedUser($request)->load(['docente', 'estudiante']);
        $perfil = $user->docente ?? $user->estudiante;
        $rutaFoto = $perfil?->foto_personal;

        abort_unless($rutaFoto !== null && Storage::disk('public')->exists($rutaFoto), 404);

        return response()->file(Storage::disk('public')->path($rutaFoto), [
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $this->authenticatedUser($request)->load(['docente', 'estudiante']);
        $validated = $request->validated();
        $nuevaFoto = $request->file('foto_personal');
        $usernameAnterior = $user->username;

        if ($user->docente !== null) {
            $docente = $user->docente;
            $fotoAnterior = $docente->foto_personal;
            $datosDocente = Arr::only($validated, [
                'nombres', 'apellido_paterno', 'apellido_materno',
                'ci', 'ci_extension', 'telefono', 'correo_personal',
                'cargo', 'especialidad', 'nivel_academico', 'fecha_ingreso',
            ]);

            if ($nuevaFoto !== null) {
                $rutaFoto = $nuevaFoto->store('fotos/docentes', 'public');
                if ($rutaFoto === false) {
                    return back()->withErrors(['foto_personal' => 'No se pudo guardar la fotografía.']);
                }
                $datosDocente['foto_personal'] = $rutaFoto;
            }

            $antes = $docente->only(array_keys($datosDocente));
            DB::transaction(fn () => $docente->update($datosDocente));

            if ($nuevaFoto !== null && $fotoAnterior !== null) {
                Storage::disk('public')->delete($fotoAnterior);
            }

            BitacoraService::registrar('actualizar_perfil_docente', 'docentes', $docente->id, $antes, $datosDocente);
        } elseif ($user->estudiante !== null) {
            $estudiante = $user->estudiante;
            $fotoAnterior = $estudiante->foto_personal;
            $datosEstudiante = Arr::only($validated, [
                'telefono', 'correo_personal', 'direccion',
            ]);

            if ($nuevaFoto !== null) {
                $rutaFoto = $nuevaFoto->store('fotos/estudiantes', 'public');
                if ($rutaFoto === false) {
                    return back()->withErrors(['foto_personal' => 'No se pudo guardar la fotografía.']);
                }
                $datosEstudiante['foto_personal'] = $rutaFoto;
            }

            $antes = $estudiante->only(array_keys($datosEstudiante));
            DB::transaction(fn () => $estudiante->update($datosEstudiante));

            if ($nuevaFoto !== null && $fotoAnterior !== null) {
                Storage::disk('public')->delete($fotoAnterior);
            }

            BitacoraService::registrar('actualizar_perfil_estudiante', 'estudiantes', $estudiante->id, $antes, $datosEstudiante);
        }

        $usernameNuevo = $validated['username'] ?? $usernameAnterior;

        if ($usernameNuevo !== $usernameAnterior) {
            $user->update(['username' => $usernameNuevo]);

            BitacoraService::registrar(
                'actualizar_username',
                'users',
                $user->id,
                ['username' => $usernameAnterior],
                ['username' => $user->username],
            );
        }

        if ($request->filled('password')) {
            $user->forceFill([
                'password' => Hash::make($validated['password']),
                'debe_cambiar_password' => false,
                'intentos_fallidos' => 0,
                'bloqueos_consecutivos' => 0,
                'bloqueado_hasta' => null,
            ])->save();

            BitacoraService::registrar('cambio_password', 'users', $user->id);
        }

        $route = $this->isStudentRoute($request) ? 'estudiante.profile.edit' : 'profile.edit';

        return Redirect::route($route)->with('status', 'profile-updated');
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

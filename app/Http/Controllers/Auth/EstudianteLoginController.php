<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EstudianteLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EstudianteLoginController extends Controller
{
    public function create(): View
    {
        $loginLockoutUntil = null;
        $username = old('username');

        if (is_string($username) && $username !== '') {
            $user = User::query()->where('username', $username)->first(['bloqueado_hasta']);

            if ($user?->bloqueado_hasta?->isFuture()) {
                $loginLockoutUntil = $user->bloqueado_hasta->timestamp;
            }
        }

        return view('auth.login-estudiante', compact('loginLockoutUntil'));
    }

    public function store(EstudianteLoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::guard('student')->user();

        if ($user && $user->debe_cambiar_password) {
            return redirect()->route('estudiante.dashboard')
                ->with('student_welcome', true)
                ->with('warning', 'Solicitud aceptada. Cambia tu usuario y contraseña temporal y registra tus preguntas de seguridad en el panel de tu perfil.');
        }

        return redirect()->intended(route('estudiante.dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::guard('student')->user();

        if ($user) {
            $user->setRememberToken(null);
            $user->save();
        }

        Auth::guard('student')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('estudiante.login');
    }
}

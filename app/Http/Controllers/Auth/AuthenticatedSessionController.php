<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\BitacoraService;
use App\Services\EstudiantePortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(EstudiantePortalService $estudiantePortal): View
    {
        $connectionStatus = $estudiantePortal->connectionStatus();
        $loginLockoutUntil = null;
        $username = old('username');

        if (is_string($username) && $username !== '') {
            $user = User::query()->where('username', $username)->first(['bloqueado_hasta']);

            if ($user?->bloqueado_hasta?->isFuture()) {
                $loginLockoutUntil = $user->bloqueado_hasta->timestamp;
            }
        }

        return view('auth.login', [
            'estudiantePortalUrl' => $connectionStatus['available'] ? $connectionStatus['portalUrl'] : null,
            'estudianteQrUrl' => $connectionStatus['available'] ? $connectionStatus['qrCodeUrl'] : null,
            'loginLockoutUntil' => $loginLockoutUntil,
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        return $this->authenticateAndRedirect($request);
    }

    private function authenticateAndRedirect(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();

        if ($user && $user->debe_cambiar_password) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Debe actualizar su contraseña temporal antes de continuar.');
        }

        if ($user && $user->tipo_usuario === 'estudiante' && Route::has('estudiante.dashboard')) {
            return redirect()->intended(route('estudiante.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();
        $rutaLogin = $user?->tipo_usuario === 'estudiante' ? 'estudiante.login' : 'login';

        if ($user) {
            BitacoraService::registrar('logout', 'users', $user->id, null, null, null, $user);
            $user->setRememberToken(null);
            $user->save();
        }

        Auth::guard('web')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route($rutaLogin);
    }
}

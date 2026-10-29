<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\BitacoraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $guard = $request->routeIs('estudiante.*') ? 'student' : 'web';
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password:'.$guard],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user($guard);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'debe_cambiar_password' => false,
            'intentos_fallidos' => 0,
            'bloqueos_consecutivos' => 0,
            'bloqueado_hasta' => null,
        ])->save();

        BitacoraService::registrar('cambio_password', 'users', $user->id);

        return back()->with('status', 'password-updated');
    }
}

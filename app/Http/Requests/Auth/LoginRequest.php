<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\BitacoraService;
use App\Services\NotificacionService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $username = (string) $this->string('username');
        $password = (string) $this->string('password');

        $user = User::query()->where('username', $username)->first();

        // RN-USU-08: Si no existe, activo=false o no pertenece al portal, rechaza sin incrementar intentos_fallidos
        if (! $user || ! $user->activo || ! $this->puedeAccederAlPortal($user)) {
            RateLimiter::hit($this->throttleKey());
            BitacoraService::registrar(
                'login_fallido',
                'users',
                $user?->id,
                null,
                ['motivo' => 'cuenta_inexistente_inactiva_o_portal_no autorizado'],
                null,
                $user
            );

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        // RF-02 / RN-USU-05: Verificar si la cuenta está temporalmente bloqueada
        if ($user->bloqueado_hasta !== null && $user->bloqueado_hasta->isFuture()) {
            $seconds = max(1, (int) now()->diffInSeconds($user->bloqueado_hasta, true));
            $minutes = (int) ceil($seconds / 60);

            BitacoraService::registrar(
                'login_bloqueado',
                'users',
                $user->id,
                null,
                ['motivo' => 'cuenta_bloqueada', 'segundos_restantes' => $seconds],
                null,
                $user
            );

            throw ValidationException::withMessages([
                'username' => "Cuenta bloqueada temporalmente. Intente nuevamente en {$minutes} min ({$seconds} s).",
            ]);
        }

        // Verificar contraseña
        if (! Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());
            $this->registrarFalloCredenciales($user);
            BitacoraService::registrar(
                'login_fallido',
                'users',
                $user->id,
                null,
                ['motivo' => 'credenciales_invalidas'],
                null,
                $user
            );

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        // Perfil asociado habilitado (RN-USU-05): estudiantes solo en estado activo;
        // docentes no inactivos, retirados ni de baja.
        if ($this->perfilNoHabilitado($user)) {
            throw ValidationException::withMessages([
                'username' => 'Su cuenta no se encuentra habilitada para iniciar sesión. Contacte al administrador.',
            ]);
        }

        // Login exitoso: reiniciar contadores y registrar ultimo_acceso (RN-USU-06, RN-USU-07)
        $user->forceFill([
            'intentos_fallidos' => 0,
            'bloqueos_consecutivos' => 0,
            'bloqueado_hasta' => null,
            'ultimo_acceso' => now(),
        ])->save();

        Auth::guard($this->guardName())->login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());

        BitacoraService::registrar('login_exitoso', 'users', $user->id, null, null, null, $user);
    }

    protected function registrarFalloCredenciales(User $user): void
    {
        $intentos = ((int) $user->intentos_fallidos) + 1;

        if ($intentos >= 5) {
            $bloqueos = min(255, ((int) $user->bloqueos_consecutivos) + 1);
            $minutos = (int) min(60, 2 ** ($bloqueos - 1)); // 1, 2, 4, 8, 16, 32, 60

            $user->forceFill([
                'intentos_fallidos' => 0,
                'bloqueos_consecutivos' => $bloqueos,
                'bloqueado_hasta' => now()->addMinutes($minutos),
            ])->save();

            NotificacionService::notificarAdmins(
                'seguridad',
                'Cuenta bloqueada por intentos fallidos',
                "El usuario {$user->username} acumuló 5 intentos fallidos y fue bloqueado por {$minutos} minuto(s).",
                '/usuarios/'.$user->id
            );

            return;
        }

        $user->forceFill([
            'intentos_fallidos' => $intentos,
        ])->save();
    }

    /**
     * Portal que consume este request: "staff" (admin/docente) o "estudiante".
     */
    protected function portal(): string
    {
        return 'staff';
    }

    protected function guardName(): string
    {
        return 'web';
    }

    protected function puedeAccederAlPortal(User $user): bool
    {
        return match ($this->portal()) {
            'administrador' => $user->tipo_usuario === 'administrador',
            'docente' => $user->tipo_usuario === 'docente',
            'estudiante' => $user->tipo_usuario === 'estudiante',
            default => in_array($user->tipo_usuario, ['administrador', 'docente'], true),
        };
    }

    protected function perfilNoHabilitado(User $user): bool
    {
        return ! $user->perfilHabilitado();
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('username')).'|'.$this->ip().'|'.$this->portal()
        );
    }
}

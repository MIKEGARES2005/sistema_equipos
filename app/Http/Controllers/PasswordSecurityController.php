<?php

namespace App\Http\Controllers;

use App\Models\PreguntaSeguridad;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PasswordSecurityController extends Controller
{
    private const TIPOS_PERMITIDOS = ['administrador', 'docente', 'estudiante'];

    private const MINUTOS_VIGENCIA = 10;

    private const MAX_INTENTOS_RESPUESTAS = 10;

    private const MENSAJE_CUENTA_INVALIDA = 'No se pudo validar la cuenta o sus preguntas de seguridad.';

    public function verifyUsername(Request $request): RedirectResponse
    {
        $request->validate(['username' => ['required', 'string']]);

        $user = User::where('username', $request->input('username'))->first();
        $preguntas = $user?->preguntasSeguridad()->orderBy('id')->get();

        if (! $this->esCuentaRecuperable($user) || $preguntas?->count() !== 3) {
            return back()->withErrors(['username' => self::MENSAJE_CUENTA_INVALIDA]);
        }

        $request->session()->put([
            'password_reset_user_id' => $user->id,
            'password_reset_expires' => now()->addMinutes(self::MINUTOS_VIGENCIA),
        ]);

        $route = $user->isEstudiante()
            ? 'estudiante.password.security.questions'
            : 'password.security.questions';

        return redirect()->route($route);
    }

    /** Verificar el usuario únicamente dentro del flujo estudiantil. */
    public function verifyStudentUsername(Request $request): RedirectResponse
    {
        $request->validate(['username' => ['required', 'string']]);

        $user = User::where('username', $request->input('username'))
            ->where('tipo_usuario', 'estudiante')
            ->first();
        $preguntas = $user?->preguntasSeguridad()->orderBy('id')->get();

        if (! $this->esCuentaRecuperable($user) || $preguntas?->count() !== 3) {
            return back()->withErrors(['username' => self::MENSAJE_CUENTA_INVALIDA]);
        }

        $request->session()->put([
            'password_reset_user_id' => $user->id,
            'password_reset_expires' => now()->addMinutes(self::MINUTOS_VIGENCIA),
        ]);

        return redirect()->route('estudiante.password.security.questions');
    }

    public function showQuestions(Request $request): View|RedirectResponse
    {
        return $this->mostrarPreguntas($request, 'auth.security-questions');
    }

    public function showStudentQuestions(Request $request): View|RedirectResponse
    {
        return $this->mostrarPreguntas($request, 'auth.estudiante.security-questions');
    }

    /** Mantener la verificación estudiantil en una acción y ruta propias. */
    public function verifyStudentAnswers(Request $request): RedirectResponse
    {
        return $this->verifyAnswers($request);
    }

    private function mostrarPreguntas(Request $request, string $view): View|RedirectResponse
    {
        $user = $this->usuarioDesdeSesion($request);

        if (! $user || ! $this->esCuentaRecuperable($user)) {
            $this->olvidarRecuperacion($request);

            return redirect()->route('password.request')->withErrors(['username' => self::MENSAJE_CUENTA_INVALIDA]);
        }

        $preguntas = $user->preguntasSeguridad()->orderBy('id')->get();

        if ($preguntas->count() !== 3) {
            $this->olvidarRecuperacion($request);

            return redirect()->route('password.request')->withErrors(['username' => self::MENSAJE_CUENTA_INVALIDA]);
        }

        return view($view, ['preguntas' => $preguntas]);
    }

    public function verifyAnswers(Request $request): RedirectResponse
    {
        $request->validate([
            'respuesta' => ['required', 'array', 'size:3'],
            'respuesta.*' => ['required', 'string'],
        ]);

        $limiterKey = $this->claveRateLimiter($request);

        if (RateLimiter::tooManyAttempts($limiterKey, self::MAX_INTENTOS_RESPUESTAS)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return back()
                ->withErrors(['respuesta' => "Demasiados intentos. Espera {$seconds} segundos antes de volver a intentarlo."])
                ->withInput();
        }

        $user = $this->usuarioDesdeSesion($request);

        if (! $user || ! $this->esCuentaRecuperable($user)) {
            $this->olvidarRecuperacion($request);

            return redirect()->route('password.request')->withErrors([
                'username' => 'La verificación expiró. Vuelve a iniciar el proceso.',
            ]);
        }

        $preguntas = $user->preguntasSeguridad()->orderBy('id')->get();

        if ($preguntas->count() !== 3) {
            $this->olvidarRecuperacion($request);

            return redirect()->route('password.request')->withErrors(['username' => self::MENSAJE_CUENTA_INVALIDA]);
        }

        foreach ($preguntas as $index => $pregunta) {
            $respuestaOriginal = trim((string) $request->input("respuesta.{$index}"));
            $respuesta = $this->normalizarRespuesta($respuestaOriginal);

            if (! $this->respuestaValida($respuestaOriginal, $respuesta, $pregunta)) {
                RateLimiter::hit($limiterKey, 60);

                $route = $user->isEstudiante()
                    ? 'estudiante.password.security.questions'
                    : 'password.security.questions';

                return redirect()->route($route)
                    ->withErrors(['respuesta' => 'Una o más respuestas son incorrectas.'])
                    ->withInput();
            }
        }

        RateLimiter::clear($limiterKey);

        $route = $user->isEstudiante()
            ? 'estudiante.password.security.reset.show'
            : 'password.security.reset.show';

        return redirect()->route($route);
    }

    public function showResetPassword(Request $request): View|RedirectResponse
    {
        return $this->mostrarResetPassword($request, 'auth.reset-password-security');
    }

    public function showStudentResetPassword(Request $request): View|RedirectResponse
    {
        return $this->mostrarResetPassword($request, 'auth.estudiante.reset-password-security');
    }

    /** Mantener el cambio de contraseña estudiantil en una acción y ruta propias. */
    public function resetStudentPassword(Request $request): RedirectResponse
    {
        return $this->resetPassword($request);
    }

    private function mostrarResetPassword(Request $request, string $view): View|RedirectResponse
    {
        $user = $this->usuarioDesdeSesion($request);

        if (! $user || ! $this->esCuentaRecuperable($user)) {
            $this->olvidarRecuperacion($request);

            return redirect()->route('password.request')->withErrors([
                'username' => 'La verificación expiró. Vuelve a responder tus preguntas de seguridad.',
            ]);
        }

        return view($view);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'confirmed', 'min:8']]);

        $user = $this->usuarioDesdeSesion($request);

        if (! $user || ! $this->esCuentaRecuperable($user)) {
            $this->olvidarRecuperacion($request);

            return redirect()->route('password.request')->withErrors([
                'username' => 'La verificación expiró. Vuelve a responder tus preguntas de seguridad.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'debe_cambiar_password' => false,
            'intentos_fallidos' => 0,
            'bloqueos_consecutivos' => 0,
            'bloqueado_hasta' => null,
        ]);
        $user->setRememberToken(null);
        $user->save();

        BitacoraService::registrar('cambio_password', 'users', $user->id, null, null, null, $user);

        $this->olvidarRecuperacion($request);

        $rutaLogin = $user->tipo_usuario === 'estudiante' ? 'estudiante.login' : 'login';

        return redirect()->route($rutaLogin)
            ->with('status', 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
    }

    private function usuarioDesdeSesion(Request $request): ?User
    {
        $userId = $request->session()->get('password_reset_user_id');
        $expires = $request->session()->get('password_reset_expires');

        if (! is_int($userId) || ! $expires || now()->greaterThan($expires)) {
            return null;
        }

        return User::find($userId);
    }

    private function normalizarRespuesta(string $respuesta): string
    {
        return Str::ascii(Str::lower(trim($respuesta)));
    }

    private function respuestaValida(string $respuestaOriginal, string $respuesta, PreguntaSeguridad $pregunta): bool
    {
        if (Hash::check($respuesta, $pregunta->respuesta_hash)) {
            return true;
        }

        $respuestasAntiguas = array_unique([
            $respuestaOriginal,
            Str::ucfirst($respuesta),
            Str::title($respuesta),
            Str::upper($respuesta),
        ]);

        foreach ($respuestasAntiguas as $respuestaAntigua) {
            if (Hash::check($respuestaAntigua, $pregunta->respuesta_hash)) {
                $pregunta->forceFill([
                    'respuesta_hash' => Hash::make($respuesta),
                ])->save();

                return true;
            }
        }

        return false;
    }

    private function claveRateLimiter(Request $request): string
    {
        return 'password-security-answers|'.($request->session()->get('password_reset_user_id') ?? 'guest').'|'.$request->ip();
    }

    private function esCuentaRecuperable(?User $user): bool
    {
        return $user !== null
            && (bool) $user->activo
            && $user->perfilHabilitado()
            && in_array($user->tipo_usuario, self::TIPOS_PERMITIDOS, true);
    }

    private function olvidarRecuperacion(Request $request): void
    {
        $request->session()->forget(['password_reset_user_id', 'password_reset_expires']);
    }
}

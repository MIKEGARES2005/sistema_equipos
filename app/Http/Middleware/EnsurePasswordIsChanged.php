<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $isStudent = $request->routeIs('estudiante.*') || $request->is('estudiante', 'estudiante/*');
        $user = $request->user($isStudent ? 'student' : null);

        if ($user && $user->debe_cambiar_password) {
            $allowedRoutes = [
                'profile.edit',
                'profile.update',
                'estudiante.profile.edit',
                'estudiante.profile.update',
                'password.update',
                'estudiante.password.update',
                'logout',
                'estudiante.logout',
                'security.questions.edit',
                'security.questions.update',
                'estudiante.security.questions.edit',
                'estudiante.security.questions.update',
                'estudiante.dashboard',
                'estudiante.notifications.details',
                'estudiante.notifications.index',
                'estudiante.notifications.clear',
            ];

            if (! $request->routeIs($allowedRoutes) && ! $request->is('logout', 'profile', 'password', 'seguridad/preguntas', 'estudiante/perfil', 'estudiante/password', 'estudiante/seguridad/preguntas')) {
                return redirect()->route($isStudent ? 'estudiante.profile.edit' : 'profile.edit')
                    ->with('warning', 'Por seguridad, debe cambiar su contraseña temporal antes de continuar.');
            }
        }

        return $next($request);
    }
}

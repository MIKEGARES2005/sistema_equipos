<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $usesStudentGuard = in_array('student', $roles, true);
        $roles = array_values(array_diff($roles, ['student']));
        $user = $request->user($usesStudentGuard ? 'student' : null);

        if (! $user) {
            abort(401);
        }

        if ($user->activo === false) {
            abort(403, 'Su cuenta se encuentra inactiva.');
        }

        if (! $user->perfilHabilitado()) {
            abort(403, 'Su perfil no se encuentra habilitado.');
        }

        $normalizedRoles = array_map(
            fn (string $role): string => $role === 'admin' ? 'administrador' : $role,
            $roles
        );

        if (! in_array($user->tipo_usuario, $normalizedRoles, true)) {
            abort(403);
        }

        return $next($request);
    }
}

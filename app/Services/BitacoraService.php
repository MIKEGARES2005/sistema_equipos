<?php

namespace App\Services;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BitacoraService
{
    public static function registrar(
        string $accion,
        ?string $entidad = null,
        ?int $entidadId = null,
        ?array $antes = null,
        ?array $despues = null,
        ?int $materiaId = null,
        ?User $actor = null
    ): Bitacora {
        $user = $actor ?? (request()?->routeIs('estudiante.*')
            ? Auth::guard('student')->user()
            : Auth::user());

        return Bitacora::create([
            'user_id' => $user?->id,
            'rol' => $user?->tipo_usuario,
            'accion' => $accion,
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'antes' => self::datosAuditables($antes),
            'despues' => self::datosAuditables($despues),
            'ip' => request()?->ip(),
            'materia_id' => $materiaId,
            'created_at' => now(),
        ]);
    }

    private static function datosAuditables(?array $datos): ?array
    {
        if ($datos === null) {
            return null;
        }

        $sensibles = [
            'password',
            'password_confirmation',
            'current_password',
            'respuesta',
            'respuesta_hash',
            'token',
            'token_cancelacion',
            'remember_token',
        ];

        $resultado = [];

        foreach ($datos as $clave => $valor) {
            $claveNormalizada = Str::lower((string) $clave);

            if (in_array($claveNormalizada, $sensibles, true)) {
                continue;
            }

            $resultado[$clave] = is_array($valor)
                ? self::datosAuditables($valor)
                : $valor;
        }

        return $resultado;
    }
}

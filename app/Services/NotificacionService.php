<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\User;

class NotificacionService
{
    public static function notificarUsuario(
        int $userId,
        string $tipo,
        string $titulo,
        string $mensaje,
        ?string $url = null
    ): Notificacion {
        return Notificacion::create([
            'user_id' => $userId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url' => $url,
        ]);
    }

    public static function notificarAdmins(
        string $tipo,
        string $titulo,
        string $mensaje,
        ?string $url = null
    ): void {
        $adminIds = User::query()
            ->where('tipo_usuario', 'administrador')
            ->where('activo', true)
            ->pluck('id');

        foreach ($adminIds as $adminId) {
            self::notificarUsuario((int) $adminId, $tipo, $titulo, $mensaje, $url);
        }
    }
}

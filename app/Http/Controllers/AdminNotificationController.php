<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\JsonResponse;

class AdminNotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notificaciones = Notificacion::query()
            ->where('user_id', auth()->id())
            ->latest('created_at')
            ->take(5)
            ->get(['id', 'tipo', 'titulo', 'mensaje', 'url', 'leida_en', 'created_at'])
            ->map(fn (Notificacion $notificacion): array => [
                'id' => $notificacion->id,
                'tipo' => $notificacion->tipo,
                'titulo' => $notificacion->titulo,
                'mensaje' => $notificacion->mensaje,
                'url' => $notificacion->url,
                'leida' => $notificacion->leida_en !== null,
                'creada' => $notificacion->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'notificaciones' => $notificaciones,
            'no_leidas' => Notificacion::query()
                ->where('user_id', auth()->id())
                ->whereNull('leida_en')
                ->count(),
        ]);
    }

    public function clear(): JsonResponse
    {
        Notificacion::query()
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json([
            'notificaciones' => [],
            'no_leidas' => 0,
        ]);
    }
}

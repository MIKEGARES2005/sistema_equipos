<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentNotificationController extends Controller
{
    public function details(Request $request): View
    {
        $user = $request->user('student')->load('estudiante');
        $notificaciones = $user->notificaciones()
            ->latest('created_at')
            ->get();

        return view('estudiante.notifications', [
            'user' => $user,
            'estudiante' => $user->estudiante,
            'notificaciones' => $notificaciones,
            'notificacionesNoLeidas' => $user->notificaciones()->whereNull('leida_en')->count(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user('student')->id;
        $notificaciones = Notificacion::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->take(8)
            ->get(['id', 'tipo', 'titulo', 'mensaje', 'url', 'leida_en', 'created_at'])
            ->map(fn (Notificacion $notificacion): array => [
                'id' => $notificacion->id,
                'tipo' => $notificacion->tipo,
                'titulo' => $notificacion->titulo,
                'resumen' => $this->notificationSummary($notificacion),
                'mensaje' => $notificacion->mensaje,
                'url' => $this->notificationDestination($notificacion),
                'leida' => $notificacion->leida_en !== null,
                'creada' => $notificacion->created_at?->diffForHumans(),
            ])
            ->values();

        return response()->json([
            'notificaciones' => $notificaciones,
            'no_leidas' => Notificacion::query()
                ->where('user_id', $userId)
                ->whereNull('leida_en')
                ->count(),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        Notificacion::query()
            ->where('user_id', $request->user('student')->id)
            ->delete();

        $respuesta = [
            'notificaciones' => [],
            'no_leidas' => 0,
        ];

        if (! $request->expectsJson()) {
            return redirect()
                ->route('estudiante.notifications.details')
                ->with('status', 'La bandeja de notificaciones fue limpiada.');
        }

        return response()->json($respuesta);
    }

    private function notificationSummary(Notificacion $notificacion): string
    {
        $contexto = mb_strtolower($notificacion->tipo.' '.$notificacion->titulo);

        if (str_contains($contexto, 'solicitud') && (str_contains($contexto, 'acept') || str_contains($contexto, 'aprob'))) {
            return 'Solicitud aceptada';
        }

        if (str_contains($contexto, 'solicitud') && (str_contains($contexto, 'rechaz') || str_contains($contexto, 'cancel'))) {
            return 'Solicitud rechazada';
        }

        if (str_contains($contexto, 'solicitud')) {
            return 'Solicitud de registro';
        }

        if (str_contains($contexto, 'asignacion') || str_contains($contexto, 'asignación')) {
            return 'Asignación aceptada';
        }

        if (str_contains($contexto, 'decision_academica') || str_contains($contexto, 'decisión académica')) {
            return 'Resultado académico';
        }

        return Str::limit($notificacion->titulo ?: 'Aviso del sistema', 30);
    }

    private function notificationDestination(Notificacion $notificacion): string
    {
        if (in_array($notificacion->tipo, ['sistema', 'solicitud_aprobada'], true) || $notificacion->url === null) {
            return route('estudiante.notifications.details');
        }

        return $notificacion->url;
    }
}

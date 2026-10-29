<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EstudianteDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user('student')->load('estudiante');
        $notificaciones = $user->notificaciones()
            ->latest('created_at')
            ->take(8)
            ->get();
        $notificacionesNoLeidas = $user->notificaciones()->whereNull('leida_en')->count();

        return view('estudiante.dashboard', [
            'user' => $user,
            'estudiante' => $user->estudiante,
            'notificaciones' => $notificaciones,
            'notificacionesNoLeidas' => $notificacionesNoLeidas,
        ]);
    }
}

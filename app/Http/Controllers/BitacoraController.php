<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BitacoraController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'accion' => ['nullable', 'string', 'max:60'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'rol' => ['nullable', 'string', Rule::in(['administrador', 'docente', 'estudiante'])],
        ]);

        $bitacoras = Bitacora::query()
            ->with('user:id,username,tipo_usuario')
            ->when($filtros['accion'] ?? null, fn ($query, $accion) => $query->where('accion', $accion))
            ->when($filtros['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filtros['rol'] ?? null, fn ($query, $rol) => $query->where('rol', $rol))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $acciones = Bitacora::query()
            ->select('accion')
            ->distinct()
            ->orderBy('accion')
            ->pluck('accion');

        $usuarios = User::query()
            ->select(['id', 'username', 'tipo_usuario'])
            ->orderBy('username')
            ->get();

        return view('admin.bitacora.index', compact('bitacoras', 'acciones', 'filtros', 'usuarios'));
    }
}

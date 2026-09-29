<?php
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccesorioController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware(['auth', 'role:administrador'])->prefix('panel/admin')->group(function () {
    Route::get('/', function () {
        return 'Panel de administrador';
    })->name('admin.panel');
});
Route::middleware(['auth', 'role:administrador,docente'])->prefix('panel')->group(function () {
    Route::get('/inicio', function () {
        return 'Panel de administración y docente';
    })->name('panel.inicio');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth', 'role:administrador,docente'])->group(function () {
    Route::get('/equipos/crear', [EquipoController::class, 'create'])->name('equipos.create');
    Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');
    Route::post('/equipos', [EquipoController::class, 'store'])->name('equipos.store');
    Route::get('/equipos/{id}', [EquipoController::class, 'show'])->name('equipos.show');
    Route::get('/equipos/{id}/editar', [EquipoController::class, 'edit'])->name('equipos.edit');
    Route::put('/equipos/{id}', [EquipoController::class, 'update'])->name('equipos.update');
    Route::delete('/equipos/{id}', [EquipoController::class, 'destroy'])->name('equipos.destroy');
    Route::get('/asignaciones', [AsignacionController::class, 'index'])->name('asignaciones.index');
    Route::get('/asignaciones/crear', [AsignacionController::class, 'create'])->name('asignaciones.create');
    Route::post('/asignaciones', [AsignacionController::class, 'store'])->name('asignaciones.store');
    Route::put('/asignaciones/{id}/finalizar', [AsignacionController::class, 'finalizar'])
        ->name('asignaciones.finalizar');
    Route::get('/accesorios', [AccesorioController::class, 'index'])
        ->name('accesorios.index');
    Route::get('/accesorios/crear', [AccesorioController::class, 'create'])
        ->name('accesorios.create');
    Route::post('/accesorios', [AccesorioController::class, 'store'])
        ->name('accesorios.store');
    Route::delete('/accesorios/{id}', [AccesorioController::class, 'destroy'])
        ->name('accesorios.destroy');
    Route::get('/incidencias', [IncidenciaController::class, 'index'])
        ->name('incidencias.index');
    Route::get('/incidencias/crear', [IncidenciaController::class, 'create'])
        ->name('incidencias.create');
    Route::post('/incidencias', [IncidenciaController::class, 'store'])
        ->name('incidencias.store');
    Route::put('/incidencias/{id}/resolver', [IncidenciaController::class, 'resolver'])
        ->name('incidencias.resolver');
    Route::delete('/incidencias/{id}', [IncidenciaController::class, 'destroy'])
        ->name('incidencias.destroy');
    Route::get('/reportes', [ReporteController::class, 'index'])
    ->name('reportes.index');
});
require __DIR__.'/auth.php';
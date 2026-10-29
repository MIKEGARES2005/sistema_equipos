<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EstudianteLoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\PasswordSecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', fn () => view('auth.forgot-password'))
        ->name('password.request');
    Route::post('forgot-password', [PasswordSecurityController::class, 'verifyUsername'])
        ->name('password.security.verify');
    Route::get('security-questions', [PasswordSecurityController::class, 'showQuestions'])
        ->name('password.security.questions');
    Route::get('reset-password-security', [PasswordSecurityController::class, 'showResetPassword'])
        ->name('password.security.reset.show');
    Route::post('security-questions', [PasswordSecurityController::class, 'verifyAnswers'])
        ->name('password.security.answers');
    Route::post('reset-password-security', [PasswordSecurityController::class, 'resetPassword'])
        ->name('password.security.reset');

});

// El portal estudiantil debe poder abrirse aunque exista una sesión administrativa.
// La autenticación de cada portal se resuelve en su propio formulario.
Route::get('estudiante/login', [EstudianteLoginController::class, 'create'])
    ->name('estudiante.login');

Route::post('estudiante/login', [EstudianteLoginController::class, 'store'])
    ->name('estudiante.login.store');

// Recuperación estudiantil independiente de cualquier sesión administrativa.
Route::get('estudiante/forgot-password', fn () => view('auth.estudiante.forgot-password'))
    ->name('estudiante.password.request');
Route::post('estudiante/forgot-password', [PasswordSecurityController::class, 'verifyStudentUsername'])
    ->name('estudiante.password.security.verify');
Route::get('estudiante/security-questions', [PasswordSecurityController::class, 'showStudentQuestions'])
    ->name('estudiante.password.security.questions');
Route::post('estudiante/security-questions', [PasswordSecurityController::class, 'verifyStudentAnswers'])
    ->name('estudiante.password.security.answers');
Route::get('estudiante/reset-password-security', [PasswordSecurityController::class, 'showStudentResetPassword'])
    ->name('estudiante.password.security.reset.show');
Route::post('estudiante/reset-password-security', [PasswordSecurityController::class, 'resetStudentPassword'])
    ->name('estudiante.password.security.reset');

Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

});

Route::put('password', [PasswordController::class, 'update'])
    ->middleware('auth')
    ->name('password.update');

Route::put('estudiante/password', [PasswordController::class, 'update'])
    ->middleware(['auth:student', 'role:estudiante,student'])
    ->name('estudiante.password.update');

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::post('estudiante/logout', [EstudianteLoginController::class, 'destroy'])
    ->middleware('auth:student')
    ->name('estudiante.logout');

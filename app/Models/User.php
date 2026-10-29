<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'password',
        'tipo_usuario',
        'docente_id',
        'estudiante_id',
        'intentos_fallidos',
        'bloqueado_hasta',
        'activo',
        'debe_cambiar_password',
        'username_cambiado_en',
        'bloqueos_consecutivos',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
            'intentos_fallidos' => 'integer',
            'bloqueado_hasta' => 'datetime',
            'debe_cambiar_password' => 'boolean',
            'username_cambiado_en' => 'datetime',
            'bloqueos_consecutivos' => 'integer',
            'ultimo_acceso' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    /**
     * Mantiene sincronizado username_cambiado_en cuando cambia el nombre de usuario.
     */
    protected static function booted(): void
    {
        static::updating(function (User $user): void {
            if ($user->isDirty('username')) {
                $user->username_cambiado_en = now();
            }
        });
    }

    public function preguntasSeguridad(): HasMany
    {
        return $this->hasMany(PreguntaSeguridad::class);
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class);
    }

    public function bitacoras(): HasMany
    {
        return $this->hasMany(Bitacora::class);
    }

    public function isAdministrador(): bool
    {
        return $this->tipo_usuario === 'administrador';
    }

    public function isDocente(): bool
    {
        return $this->tipo_usuario === 'docente' || $this->esDocenteEfectivo();
    }

    /**
     * RN-USU-13: un administrador tambien ejerce como docente si tiene perfil asociado.
     */
    public function esDocenteEfectivo(): bool
    {
        return $this->docente_id !== null;
    }

    public function isEstudiante(): bool
    {
        return $this->tipo_usuario === 'estudiante';
    }

    public function canAccessStaffPortal(): bool
    {
        return $this->activo && ($this->isAdministrador() || $this->isDocente());
    }

    /**
     * Indica si el perfil personal asociado permite el acceso al sistema.
     * Los estudiantes solo acceden en estado "activo".
     */
    public function perfilHabilitado(): bool
    {
        if ($this->estudiante_id !== null) {
            return $this->estudiante?->estado === 'activo';
        }

        if ($this->docente_id !== null) {
            return ! in_array($this->docente?->estado, ['inactivo', 'retirado', 'baja'], true);
        }

        return true;
    }

    public function nombreCompleto(): string
    {
        if ($this->docente) {
            return $this->docente->nombreCompleto();
        }

        if ($this->estudiante) {
            return $this->estudiante->nombreCompleto();
        }

        return $this->username;
    }

    public function getNameAttribute(): string
    {
        return $this->nombreCompleto();
    }
}

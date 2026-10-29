<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estudiante extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'matricula',
        'semestre',
        'generacion_ingreso',
        'ci',
        'ci_extension',
        'telefono',
        'correo_institucional',
        'correo_personal',
        'direccion',
        'foto_personal',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'estudiante_solicitante_id');
    }

    public function solicitudIntegrantes(): HasMany
    {
        return $this->hasMany(SolicitudIntegrante::class, 'estudiante_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function historialSeguimiento(): HasMany
    {
        return $this->hasMany(HistorialSeguimientoEstudiante::class);
    }

    public function nombreCompleto(): string
    {
        $parts = array_filter([
            $this->nombres,
            $this->apellido_paterno,
            $this->apellido_materno,
        ]);

        return trim(implode(' ', $parts));
    }
}

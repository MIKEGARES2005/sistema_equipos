<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Docente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'ci_extension',
        'telefono',
        'correo_personal',
        'cargo',
        'especialidad',
        'nivel_academico',
        'fecha_ingreso',
        'estado',
        'foto_personal',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
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

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
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

    public function esTemporal(): bool
    {
        return $this->cargo === 'temporal';
    }
}

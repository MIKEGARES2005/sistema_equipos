<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materias';

    public const SEMESTRES = [
        1 => 'Primero',
        2 => 'Segundo',
        3 => 'Tercero',
        4 => 'Cuarto',
    ];

    protected $fillable = [
        'nombre',
        'sigla',
        'semestre',
        'estado',
    ];

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function asignaciones(): HasManyThrough
    {
        return $this->hasManyThrough(Asignacion::class, Horario::class);
    }

    public function getSemestreNombreAttribute(): string
    {
        return self::SEMESTRES[(int) $this->semestre] ?? (string) $this->semestre;
    }
}

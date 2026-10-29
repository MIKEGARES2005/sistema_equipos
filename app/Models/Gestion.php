<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Gestion extends Model
{
    use HasFactory;

    protected $table = 'gestiones';

    protected $fillable = [
        'codigo',
        'anio',
        'periodo',
        'fecha_inicio',
        'fecha_fin',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activa' => 'boolean',
        ];
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class);
    }

    public function solicitudes(): HasManyThrough
    {
        return $this->hasManyThrough(Solicitud::class, Horario::class);
    }

    public static function activa(): ?self
    {
        return self::query()->where('activa', true)->first()
            ?? self::query()->orderByDesc('anio')->orderByDesc('periodo')->first();
    }

    public static function vigente(): ?self
    {
        return self::activa();
    }

    public static function vigenteId(): ?int
    {
        return self::activa()?->id;
    }
}

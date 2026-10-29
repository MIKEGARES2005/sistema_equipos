<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Alerta extends Model
{
    use HasFactory;

    protected $table = 'alertas';

    protected $fillable = [
        'equipo_id',
        'asignacion_id',
        'codigo_regla',
        'severidad',
        'mensaje',
        'estado',
        'abierta_en',
        'atendida_por',
        'atendida_en',
        'cerrada_por',
        'cerrada_en',
    ];

    protected function casts(): array
    {
        return [
            'abierta_en' => 'datetime',
            'atendida_en' => 'datetime',
            'cerrada_en' => 'datetime',
        ];
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class);
    }

    public function atendidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendida_por');
    }

    public function cerradaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrada_por');
    }

    public function incidencia(): HasOne
    {
        return $this->hasOne(Incidencia::class);
    }

    public function scopeAbiertas($query)
    {
        return $query->whereIn('estado', ['abierta', 'atendida']);
    }

    public function scopeCriticas($query)
    {
        return $query->where('severidad', 'critica');
    }

    public function scopePorRegla($query, string $codigo)
    {
        return $query->where('codigo_regla', strtoupper($codigo));
    }
}

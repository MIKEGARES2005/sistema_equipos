<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incidencia extends Model
{
    use HasFactory;

    protected $table = 'incidencias';

    protected $fillable = [
        'activo_id',
        'asignacion_id',
        'alerta_id',
        'tipo',
        'descripcion',
        'prioridad',
        'estado',
        'origen',
        'reportado_por',
        'resuelto_por',
        'fecha_reporte',
        'fecha_resolucion',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_reporte' => 'datetime',
            'fecha_resolucion' => 'datetime',
        ];
    }

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class);
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class);
    }

    public function alerta(): BelongsTo
    {
        return $this->belongsTo(Alerta::class);
    }

    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportado_por');
    }

    public function resueltoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resuelto_por');
    }

    public function scopeAbiertas($query)
    {
        return $query->whereIn('estado', ['abierta', 'en_revision']);
    }

    public function scopePorPrioridad($query, string $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    public function getEstaAbiertaAttribute(): bool
    {
        return in_array($this->estado, ['abierta', 'en_revision'], true);
    }
}

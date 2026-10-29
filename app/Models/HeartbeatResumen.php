<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeartbeatResumen extends Model
{
    use HasFactory;

    protected $table = 'heartbeats_resumen';

    protected $fillable = [
        'equipo_id',
        'hora',
        'muestras',
        'bateria_min',
        'bateria_prom',
        'cpu_prom',
        'ram_prom',
        'almacenamiento_max',
        'temperatura_max',
        'segundos_offline',
    ];

    protected function casts(): array
    {
        return [
            'hora' => 'datetime',
            'muestras' => 'integer',
            'segundos_offline' => 'integer',
        ];
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function scopeOffline($query)
    {
        return $query->where('muestras', 0);
    }
}

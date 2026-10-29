<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Heartbeat extends Model
{
    use HasFactory;

    public const ONLINE_TIMEOUT_MINUTES = 5;

    public $timestamps = false;

    protected $table = 'heartbeats';

    protected $fillable = [
        'equipo_id',
        'cpu_uso',
        'ram_uso',
        'almacenamiento_uso',
        'bateria',
        'temperatura',
        'cargando',
        'ip',
        'metricas',
        'fecha_hora',
    ];

    protected function casts(): array
    {
        return [
            'cargando' => 'boolean',
            'metricas' => 'array',
            'fecha_hora' => 'datetime',
        ];
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
}

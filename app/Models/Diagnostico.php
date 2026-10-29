<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnostico extends Model
{
    use HasFactory;

    protected $table = 'diagnosticos';

    protected $fillable = [
        'equipo_id',
        'heartbeat_id',
        'tipo',
        'nivel',
        'codigo_regla',
        'descripcion',
        'fecha_hora',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
        ];
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function heartbeat(): BelongsTo
    {
        return $this->belongsTo(Heartbeat::class);
    }

    public function scopeCriticos($query)
    {
        return $query->where('nivel', 'critica');
    }

    public function scopePorRegla($query, string $codigo)
    {
        return $query->where('codigo_regla', strtoupper($codigo));
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('fecha_hora');
    }
}

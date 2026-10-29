<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudIntegrante extends Model
{
    use HasFactory;

    protected $table = 'solicitud_integrantes';

    protected $fillable = [
        'solicitud_id',
        'estudiante_id',
        'estado',
        'atendido_por',
        'fecha_decision',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_decision' => 'datetime',
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function atendidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }
}

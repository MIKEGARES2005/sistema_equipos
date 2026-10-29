<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'horario_id',
        'estudiante_solicitante_id',
        'modalidad',
        'codigo_grupo',
        'tipo_equipo',
        'fecha_uso',
        'equipo_id',
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

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    public function estudianteSolicitante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_solicitante_id');
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function atendidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }

    public function integrantes(): HasMany
    {
        return $this->hasMany(SolicitudIntegrante::class);
    }

    public function asignacion(): HasOne
    {
        return $this->hasOne(Asignacion::class);
    }
}

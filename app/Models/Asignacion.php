<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignacion extends Model
{
    use HasFactory;

    protected $table = 'asignaciones';

    protected $fillable = [
        'codigo',
        'tipo',
        'modalidad',
        'equipo_id',
        'complemento_id',
        'estudiante_id',
        'invitado_id',
        'horario_id',
        'solicitud_id',
        'codigo_grupo',
        'origen',
        'motivo',
        'autorizado_por',
        'entregado_por',
        'fecha_entrega',
        'entrega_confirmada_at',
        'fecha_devolucion_prevista',
        'fecha_devolucion_real',
        'devolucion_recibida_por',
        'devolucion_aprobada_por',
        'estado',
        'estado_fisico_entrega',
        'estado_fisico_devolucion',
        'observaciones',
        'motivo_devolucion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_entrega' => 'datetime',
            'entrega_confirmada_at' => 'datetime',
            'fecha_devolucion_prevista' => 'datetime',
            'fecha_devolucion_real' => 'datetime',
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function invitado(): BelongsTo
    {
        return $this->belongsTo(Invitado::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    public function entregadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entregado_por');
    }

    public function devolucionRecibidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'devolucion_recibida_por');
    }

    public function devolucionAprobadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'devolucion_aprobada_por');
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(AsignacionChecklist::class);
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }

    public function getDevolucionAprobadaAttribute(): bool
    {
        return ! is_null($this->devolucion_aprobada_por);
    }

    /**
     * Asignaciones vinculadas a los horarios de un docente.
     */
    public function scopeForTeacher(Builder $query, User $user): Builder
    {
        return $query->whereHas(
            'horario',
            fn (Builder $query) => $query->where('docente_id', $user->docente_id)
        );
    }

    public function getEstaAbiertaAttribute(): bool
    {
        return in_array($this->estado, ['pendiente_entrega', 'activa', 'devolucion_pendiente', 'vencida', 'con_incidencia'], true);
    }
}

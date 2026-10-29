<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionChecklist extends Model
{
    use HasFactory;

    protected $table = 'asignacion_checklist';

    protected $fillable = [
        'asignacion_id',
        'complemento_id',
        'momento',
        'presente',
        'estado',
        'observacion',
        'verificado_por',
        'verificado_at',
    ];

    protected function casts(): array
    {
        return [
            'presente' => 'boolean',
            'verificado_at' => 'datetime',
        ];
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class);
    }

    public function complemento(): BelongsTo
    {
        return $this->belongsTo(Complemento::class);
    }

    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificado_por');
    }

    public function scopePendientes($query, string $momento)
    {
        return $query->where('momento', $momento)->whereNull('presente');
    }
}

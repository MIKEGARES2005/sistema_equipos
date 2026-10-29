<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipoComplemento extends Model
{
    use HasFactory;

    protected $table = 'equipo_complemento';

    protected $fillable = [
        'equipo_id',
        'complemento_id',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }

    public function complemento(): BelongsTo
    {
        return $this->belongsTo(Complemento::class);
    }

    public function scopeVigentes($query)
    {
        return $query->whereNull('fecha_fin');
    }
}

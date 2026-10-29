<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complemento extends Model
{
    use HasFactory;

    protected $table = 'complementos';

    protected $fillable = [
        'activo_id',
        'tipo_complemento',
        'estado_fisico',
        'observaciones',
    ];

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class);
    }

    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'equipo_complemento')
            ->withPivot(['fecha_inicio', 'fecha_fin', 'observaciones'])
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';

    protected $fillable = [
        'activo_id',
        'identificador',
        'tipo_equipo',
        'modo_uso',
        'mac',
        'es_servidor',
        'token_hash',
        'ultima_conexion',
        'ip_ultima',
    ];

    protected function casts(): array
    {
        return [
            'es_servidor' => 'boolean',
            'ultima_conexion' => 'datetime',
        ];
    }

    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class);
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(Heartbeat::class);
    }

    public function diagnosticos(): HasMany
    {
        return $this->hasMany(Diagnostico::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    public function complementos()
    {
        return $this->belongsToMany(Complemento::class, 'equipo_complemento')
            ->withPivot(['fecha_inicio', 'fecha_fin', 'observaciones'])
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'activos';

    protected $fillable = [
        'codigo',
        'categoria_id',
        'nombre',
        'marca',
        'modelo',
        'numero_serie',
        'estado_uso',
        'ubicacion',
        'fecha_adquisicion',
        'fecha_baja',
        'imagen_referencial',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_adquisicion' => 'date',
            'fecha_baja' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function equipo(): HasOne
    {
        return $this->hasOne(Equipo::class);
    }

    public function complemento(): HasOne
    {
        return $this->hasOne(Complemento::class);
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }
}

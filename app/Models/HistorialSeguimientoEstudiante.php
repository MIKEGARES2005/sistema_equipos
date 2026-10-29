<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialSeguimientoEstudiante extends Model
{
    use HasFactory;

    protected $table = 'historial_seguimiento_estudiantes';

    protected $fillable = [
        'estudiante_id',
        'gestion_id',
        'tipo_evento',
        'descripcion',
        'fecha_evento',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_evento' => 'date',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(Gestion::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}

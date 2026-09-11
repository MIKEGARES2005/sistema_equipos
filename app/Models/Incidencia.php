<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Incidencia extends Model
{
    protected $fillable = [
        'equipo_id',
        'asignacion_id',
        'tipo',
        'descripcion',
        'prioridad',
        'estado',
        'fecha_reporte',
        'fecha_resolucion',
        'observaciones',
    ];
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class);
    }
}
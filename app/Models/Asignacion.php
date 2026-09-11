<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Asignacion extends Model
{
    protected $table = 'asignaciones';
    protected $fillable = [
        'equipo_id',
        'estudiante_id',
        'materia_id',
        'grupo',
        'origen',
        'fecha_solicitud',
        'fecha_aprobacion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'observaciones',
    ];
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }
    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }
}
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Materia extends Model
{
    protected $fillable = [
        'nombre',
        'semestre',
        'docente_id',
        'hora_inicio',
        'hora_fin',
        'estado',
    ];
    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }
}
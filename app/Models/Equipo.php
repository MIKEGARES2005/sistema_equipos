<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Equipo extends Model
{
    protected $fillable = [
        'codigo',
        'tipo_equipo',
        'marca',
        'modelo',
        'numero_serie',
        'modo_uso',
        'estado',
        'mac',
        'imagen_referencial',
        'ultima_conexion',
    ];
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }
    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class);
    }
    public function accesorios(): HasMany
    {
        return $this->hasMany(Accesorio::class);
    }
    public function heartbeats(): HasMany
    {
        return $this->hasMany(Heartbeat::class);
    }
    public function diagnosticos(): HasMany
    {
        return $this->hasMany(Diagnostico::class);
    }
}
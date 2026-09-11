<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Estudiante extends Model
{
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'matricula',
        'semestre',
        'ci',
        'telefono',
        'correo_institucional',
        'correo_personal',
        'estado',
    ];
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }
}
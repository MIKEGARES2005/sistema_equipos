<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Docente extends Model
{
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'telefono',
        'cargo',
        'estado',
    ];
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class);
    }
}
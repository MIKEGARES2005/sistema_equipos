<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Accesorio extends Model
{
    protected $fillable = [
        'equipo_id',
        'nombre',
        'marca',
        'modelo',
        'numero_serie',
        'cantidad',
        'estado',
        'observaciones',
    ];
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
}
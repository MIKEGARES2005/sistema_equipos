<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Heartbeat extends Model
{
    protected $fillable = [
        'equipo_id',
        'cpu_uso',
        'ram_uso',
        'almacenamiento_uso',
        'bateria',
        'temperatura',
        'fecha_hora',
    ];
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
}
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Diagnostico extends Model
{
    protected $fillable = [
        'equipo_id',
        'heartbeat_id',
        'tipo',
        'nivel',
        'descripcion',
        'fecha_hora',
    ];
    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Equipo::class);
    }
    public function heartbeat(): BelongsTo
    {
        return $this->belongsTo(Heartbeat::class);
    }
}
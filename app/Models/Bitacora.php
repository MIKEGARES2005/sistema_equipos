<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'bitacora';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'rol',
        'accion',
        'entidad',
        'entidad_id',
        'antes',
        'despues',
        'ip',
        'materia_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'antes' => 'array',
            'despues' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function scopeAccion($query, string $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopeUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('created_at');
    }
}

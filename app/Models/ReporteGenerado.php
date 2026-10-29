<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteGenerado extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'reportes_generados';

    protected $fillable = [
        'tipo',
        'parametros',
        'periodo_inicio',
        'periodo_fin',
        'generado_por',
        'ruta',
        'vence_en',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'parametros' => 'array',
            'periodo_inicio' => 'date',
            'periodo_fin' => 'date',
            'vence_en' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por');
    }

    public function scopeVencidos($query)
    {
        return $query->whereNotNull('vence_en')->where('vence_en', '<', now());
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('created_at');
    }
}

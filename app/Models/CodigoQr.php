<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodigoQr extends Model
{
    protected $table = 'codigos_qr';

    protected $fillable = [
        'tipo',
        'token_hash',
        'activo_id',
        'horario_id',
        'fecha_uso',
        'expira_en',
        'activo',
        'creado_por',
        'activo_qr_unico',
        'clase_qr_unica',
    ];

    protected $casts = [
        'fecha_uso' => 'date',
        'expira_en' => 'datetime',
        'activo' => 'boolean',
    ];

    public function activoRel(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_id');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}

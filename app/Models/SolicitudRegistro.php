<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudRegistro extends Model
{
    protected $table = 'solicitudes_registro';

    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'ci_extension',
        'telefono',
        'matricula',
        'semestre',
        'generacion_ingreso',
        'correo_institucional',
        'correo_personal',
        'direccion',
        'estado',
        'motivo_rechazo',
        'revisado_por',
        'fecha_revision',
        'usuario_creado_id',
        'ci_pendiente',
        'matricula_pendiente',
        'token_cancelacion',
    ];

    protected $casts = [
        'semestre' => 'integer',
        'fecha_revision' => 'datetime',
    ];

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function usuarioCreado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creado_id');
    }
}

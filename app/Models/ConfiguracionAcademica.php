<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionAcademica extends Model
{
    protected $table = 'configuracion_academica';

    protected $fillable = [
        'gestion_vigente',
    ];

    /**
     * Gestión académica vigente para todas las inscripciones nuevas.
     */
    public static function gestionVigente(): string
    {
        return (string) (static::query()->find(1)?->gestion_vigente ?? '2026-II');
    }

    public static function actualizarGestionVigente(string $codigo): self
    {
        return static::query()->updateOrCreate(
            ['id' => 1],
            ['gestion_vigente' => $codigo],
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscripciones';

    protected $fillable = [
        'estudiante_id',
        'materia_id',
        'gestion_id',
        'gestion',
        'tipo',
        'estado',
        'resultado_reportado',
        'resultado_reportado_por',
        'fecha_reporte',
        'resultado_final',
        'revisado_por',
        'fecha_revision',
        'fecha_inscripcion',
        'observacion_revision',
        'parcial_1',
        'parcial_1_por',
        'parcial_1_en',
        'parcial_2',
        'parcial_2_por',
        'parcial_2_en',
        'nota_final',
        'nota_final_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'date',
            'fecha_reporte' => 'datetime',
            'fecha_revision' => 'datetime',
            'parcial_1' => 'decimal:2',
            'parcial_2' => 'decimal:2',
            'nota_final' => 'decimal:2',
            'parcial_1_en' => 'datetime',
            'parcial_2_en' => 'datetime',
            'nota_final_en' => 'datetime',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(Gestion::class, 'gestion_id');
    }

    public function reportadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resultado_reportado_por');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function primerParcialPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parcial_1_por');
    }

    public function segundoParcialPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parcial_2_por');
    }

    public function tieneAmbosParciales(): bool
    {
        return $this->parcial_1 !== null && $this->parcial_2 !== null;
    }

    public function calcularNotaFinal(): ?float
    {
        if (! $this->tieneAmbosParciales()) {
            return null;
        }

        return round(((float) $this->parcial_1 + (float) $this->parcial_2) / 2, 2);
    }

    public function notaMinimaAprobacion(): float
    {
        return (float) config('academica.nota_minima_aprobacion', 56);
    }

    public function resultadoSegunNotaFinal(): ?string
    {
        $nota = $this->calcularNotaFinal();

        if ($nota === null) {
            return null;
        }

        return $nota >= $this->notaMinimaAprobacion() ? 'Aprobado' : 'Reprobado';
    }
}

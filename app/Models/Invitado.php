<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invitados';

    protected $fillable = [
        'tipo',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'ci_extension',
        'telefono',
        'semestre',
        'procedencia',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'semestre' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function nombreCompleto(): string
    {
        $parts = array_filter([
            $this->nombres,
            $this->apellido_paterno,
            $this->apellido_materno,
        ]);

        return trim(implode(' ', $parts));
    }
}

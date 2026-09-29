<?php
namespace App\Models;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable([
    'name',
    'username',
    'password',
    'tipo_usuario',
    'docente_id',
    'estudiante_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
    public function isAdministrador(): bool
    {
        return $this->tipo_usuario === 'administrador';
    }

    public function isDocente(): bool
    {
        return $this->tipo_usuario === 'docente';
    }

    public function isEstudiante(): bool
    {
        return $this->tipo_usuario === 'estudiante';
    }
}
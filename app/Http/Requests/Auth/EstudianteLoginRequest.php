<?php

namespace App\Http\Requests\Auth;

class EstudianteLoginRequest extends LoginRequest
{
    protected function portal(): string
    {
        return 'estudiante';
    }

    protected function guardName(): string
    {
        return 'student';
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Docente;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->authenticatedUser() !== null;
    }

    public function rules(): array
    {
        $user = $this->authenticatedUser();
        $guard = $this->routeIs('estudiante.*') ? 'student' : 'web';

        $reglasCuenta = [
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique(User::class, 'username')->ignore($user->id),
                Rule::when(
                    $this->input('username') !== $user->username,
                    ['alpha_dash'],
                ),
            ],
            'current_password' => ['nullable', 'required_with:password,password_confirmation', 'current_password:'.$guard],
            'password' => ['nullable', 'required_with:current_password,password_confirmation', Password::defaults(), 'confirmed'],
            'password_confirmation' => ['nullable', 'required_with:password'],
        ];

        if ($user->docente_id !== null) {
            return array_merge($reglasCuenta, [
                'nombres' => ['required', 'string', 'max:100'],
                'apellido_paterno' => ['required', 'string', 'max:100'],
                'apellido_materno' => ['nullable', 'string', 'max:100'],
                'ci' => [
                    'required', 'string', 'max:20',
                    Rule::unique(Docente::class, 'ci')->ignore($user->docente_id, 'id'),
                ],
                'ci_extension' => [
                    'nullable',
                    Rule::in(['LP', 'OR', 'PT', 'CB', 'SC', 'TJ', 'CH', 'BE', 'PD']),
                ],
                'telefono' => ['nullable', 'string', 'max:20'],
                'correo_personal' => ['nullable', 'email', 'max:150'],
                'cargo' => ['required', 'string', 'max:100'],
                'especialidad' => ['nullable', 'string', 'max:150'],
                'nivel_academico' => ['nullable', 'string', 'max:100'],
                'fecha_ingreso' => ['nullable', 'date'],
                'foto_personal' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
        }

        if ($user->estudiante_id !== null) {
            return array_merge($reglasCuenta, [
                'telefono' => ['nullable', 'string', 'max:20'],
                'correo_personal' => ['nullable', 'email', 'max:150'],
                'direccion' => ['nullable', 'string', 'max:255'],
                'foto_personal' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
        }

        return $reglasCuenta;
    }

    private function authenticatedUser(): ?User
    {
        return $this->routeIs('estudiante.*')
            ? $this->user('student')
            : $this->user();
    }
}

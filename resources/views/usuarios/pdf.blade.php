@php
    $registroPersonal = match ($usuario->tipo_usuario) {
        'docente' => $usuario->docente,
        'estudiante' => $usuario->estudiante,
        'administrador' => $usuario->docente ?? $usuario->estudiante,
        default => null,
    };

    $nombreCompleto = $registroPersonal
        ? trim(collect([
            $registroPersonal->nombres ?? null,
            $registroPersonal->apellido_paterno ?? null,
            $registroPersonal->apellido_materno ?? null,
        ])->filter()->implode(' '))
        : '';
    $nombreMostrado = $nombreCompleto ?: ($usuario->name ?: $usuario->username);
    $perfilActivo = $registroPersonal
        ? in_array(strtolower((string) $registroPersonal->estado), ['activo', 'activa'], true)
        : (bool) $usuario->activo;
    $estadoCuenta = $perfilActivo ? 'Activo' : 'Inactivo';
    $fotoDataUri = null;

    if ($registroPersonal?->foto_personal && \Illuminate\Support\Facades\Storage::disk('public')->exists($registroPersonal->foto_personal)) {
        $extension = strtolower(pathinfo($registroPersonal->foto_personal, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
        $fotoDataUri = 'data:'.$mime.';base64,'.base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($registroPersonal->foto_personal));
    }

    $iniciales = collect(explode(' ', $nombreMostrado))
        ->filter()
        ->map(fn ($parte) => mb_substr($parte, 0, 1))
        ->take(2)
        ->join('');

    $datosPerfil = [];

    if ($registroPersonal) {
        $datosPerfil = $usuario->tipo_usuario === 'estudiante'
            ? [
                'Nombres' => $registroPersonal->nombres,
                'Apellido paterno' => $registroPersonal->apellido_paterno,
                'Apellido materno' => $registroPersonal->apellido_materno,
                'Carnet de identidad' => trim($registroPersonal->ci.' '.($registroPersonal->ci_extension ?? '')),
                'Matrícula' => $registroPersonal->matricula,
                'Semestre' => $registroPersonal->semestre,
                'Generación de ingreso' => $registroPersonal->generacion_ingreso,
                'Teléfono' => $registroPersonal->telefono,
                'Correo institucional' => $registroPersonal->correo_institucional,
                'Correo personal' => $registroPersonal->correo_personal,
                'DirecciÃ³n' => $registroPersonal->direccion,
            ]
            : [
                'Nombres' => $registroPersonal->nombres,
                'Apellido paterno' => $registroPersonal->apellido_paterno,
                'Apellido materno' => $registroPersonal->apellido_materno,
                'Carnet de identidad' => trim($registroPersonal->ci.' '.($registroPersonal->ci_extension ?? '')),
                'Teléfono' => $registroPersonal->telefono,
                'Correo personal' => $registroPersonal->correo_personal,
                'Cargo' => $registroPersonal->cargo,
                'Especialidad' => $registroPersonal->especialidad,
                'Nivel académico' => $registroPersonal->nivel_academico,
                'Fecha de ingreso' => $registroPersonal->fecha_ingreso
                    ? \Illuminate\Support\Carbon::parse($registroPersonal->fecha_ingreso)->format('d/m/Y')
                    : null,
            ];

        $datosPerfil['Estado'] = $estadoCuenta;
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Plantilla de usuario - {{ $nombreMostrado }}</title>
    <style>
        @page { margin: 28px 34px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #24304b; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { padding: 22px 24px; color: #ffffff; background: #111c3a; border-radius: 16px; }
        .brand { color: #7ee7f5; font-size: 9px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        h1 { margin: 8px 0 3px; font-size: 22px; }
        .subtitle { color: #c9d5f5; font-size: 10px; }
        .profile { width: 100%; margin-top: 18px; padding: 18px; border: 1px solid #dbe3f0; border-radius: 16px; background: #ffffff; }
        .profile-photo { width: 96px; height: 96px; border-radius: 14px; background: #e8effc; color: #272f7a; text-align: center; vertical-align: middle; font-size: 28px; font-weight: bold; }
        .profile-photo img { width: 96px; height: 96px; border-radius: 14px; object-fit: cover; }
        .profile-name { padding-left: 18px; vertical-align: middle; }
        .eyebrow { color: #5969b3; font-size: 8px; font-weight: bold; letter-spacing: 1.4px; text-transform: uppercase; }
        .profile-name h2 { margin: 6px 0; color: #17213d; font-size: 18px; }
        .muted { color: #71809d; }
        .badge { display: inline-block; margin-top: 5px; padding: 5px 9px; border-radius: 8px; color: #047857; background: #dff8ec; font-size: 9px; font-weight: bold; }
        .badge-inactive { color: #b42318; background: #fde4e4; }
        .section { margin-top: 16px; padding: 16px; border: 1px solid #dbe3f0; border-radius: 16px; }
        .section-title { margin: 0 0 3px; color: #17213d; font-size: 13px; font-weight: bold; }
        .section-subtitle { margin: 0 0 12px; color: #71809d; }
        .account-cell, .data-cell { width: 33.333%; padding: 10px; border: 1px solid #e2e8f2; background: #f7f9fc; }
        .data-cell { width: 50%; }
        .cell-label { color: #8290a9; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: .7px; }
        .cell-value { margin-top: 5px; color: #26334e; font-size: 10px; font-weight: bold; }
        .notice { margin-top: 16px; padding: 11px 14px; border: 1px solid #cdd8f6; border-radius: 12px; color: #4b5a78; background: #eef2ff; line-height: 1.5; }
        .footer { margin-top: 20px; color: #91a0b9; font-size: 8px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">ITIBB · Centro de control</div>
        <h1>Plantilla de usuario</h1>
        <div class="subtitle">Ficha oficial de cuenta y datos personales</div>
    </div>

    <table class="profile" cellspacing="0" cellpadding="0">
        <tr>
            <td class="profile-photo">
                @if ($fotoDataUri)
                    <img src="{{ $fotoDataUri }}" alt="Fotografía de {{ $nombreMostrado }}">
                @else
                    {{ $iniciales ?: 'U' }}
                @endif
            </td>
            <td class="profile-name">
                <div class="eyebrow">Perfil de usuario</div>
                <h2>{{ $nombreMostrado }}</h2>
                <div class="muted">{{ '@'.$usuario->username }} · {{ ucfirst($usuario->tipo_usuario ?? 'No definido') }}</div>
                <span class="badge {{ $perfilActivo ? '' : 'badge-inactive' }}">{{ $estadoCuenta }}</span>
            </td>
        </tr>
    </table>

    <div class="section">
        <h2 class="section-title">Información de la cuenta</h2>
        <p class="section-subtitle">Datos asociados al acceso al sistema.</p>
        <table width="100%" cellspacing="6" cellpadding="0">
            <tr>
                <td class="account-cell"><div class="cell-label">Nombre de usuario</div><div class="cell-value">{{ $usuario->username ?: 'No registrado' }}</div></td>
                <td class="account-cell"><div class="cell-label">Tipo de usuario</div><div class="cell-value">{{ ucfirst($usuario->tipo_usuario ?? 'No definido') }}</div></td>
                <td class="account-cell"><div class="cell-label">Última actualización</div><div class="cell-value">{{ $usuario->updated_at?->format('d/m/Y H:i') ?? 'No disponible' }}</div></td>
            </tr>
        </table>
    </div>

    @if ($datosPerfil)
        <div class="section">
            <h2 class="section-title">Información del {{ $usuario->tipo_usuario === 'estudiante' ? 'estudiante' : 'docente' }}</h2>
            <p class="section-subtitle">Datos personales y registrados en el sistema.</p>
            <table width="100%" cellspacing="6" cellpadding="0">
                @foreach (array_chunk($datosPerfil, 2, true) as $fila)
                    <tr>
                        @foreach ($fila as $etiqueta => $valor)
                            <td class="data-cell"><div class="cell-label">{{ $etiqueta }}</div><div class="cell-value">{{ $valor ?: 'No registrado' }}</div></td>
                        @endforeach
                        @if (count($fila) === 1)
                            <td class="data-cell" style="border-color: transparent; background: transparent;"></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <div class="notice"><strong>Seguridad:</strong> las contraseñas y las respuestas a las preguntas de seguridad no se muestran por este medio.</div>
    <div class="footer">Documento generado desde el Centro de control ITIBB · {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>

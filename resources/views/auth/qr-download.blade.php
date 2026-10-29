<svg xmlns="http://www.w3.org/2000/svg" width="900" height="1200" viewBox="0 0 900 1200" role="img" aria-labelledby="title description">
    <title id="title">Acceso estudiantil</title>
    <desc id="description">Código QR para ingresar al portal del estudiante.</desc>
    <defs>
        <linearGradient id="background" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0" stop-color="#0f172a" />
            <stop offset="0.55" stop-color="#172554" />
            <stop offset="1" stop-color="#312e81" />
        </linearGradient>
        <linearGradient id="accent" x1="0" x2="1">
            <stop offset="0" stop-color="#22d3ee" />
            <stop offset="1" stop-color="#818cf8" />
        </linearGradient>
        <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="18" flood-color="#020617" flood-opacity="0.22" stdDeviation="18" />
        </filter>
    </defs>
    <rect width="900" height="1200" fill="#f8fafc" />
    <rect x="50" y="50" width="800" height="1100" rx="52" fill="#ffffff" filter="url(#shadow)" />
    <path d="M102 50h696a52 52 0 0 1 52 52v188H50V102a52 52 0 0 1 52-52Z" fill="url(#background)" />
    <circle cx="125" cy="135" r="38" fill="url(#accent)" />
    <text x="125" y="145" fill="#0f172a" font-family="Arial, sans-serif" font-size="24" font-weight="800" text-anchor="middle">ITIBB</text>
    <text x="190" y="125" fill="#a5f3fc" font-family="Arial, sans-serif" font-size="18" font-weight="700" letter-spacing="4">INSTITUTO TECNOLÓGICO</text>
    <text x="190" y="177" fill="#ffffff" font-family="Arial, sans-serif" font-size="42" font-weight="800">ACCESO ESTUDIANTIL</text>
    <text x="190" y="215" fill="#cbd5e1" font-family="Arial, sans-serif" font-size="21">Escanea para ingresar al portal</text>
    <rect x="145" y="320" width="610" height="610" rx="34" fill="#ffffff" stroke="#dbeafe" stroke-width="4" />
    <rect x="180" y="355" width="540" height="540" rx="18" fill="#ffffff" />
    <image href="{{ $qrDataUri }}" x="180" y="355" width="540" height="540" preserveAspectRatio="xMidYMid meet" />
    <text x="450" y="978" fill="#0f172a" font-family="Arial, sans-serif" font-size="25" font-weight="800" text-anchor="middle">Conecta tu celular a la cámara</text>
    <text x="450" y="1020" fill="#475569" font-family="Arial, sans-serif" font-size="21" text-anchor="middle">1. Abre la cámara · 2. Escanea el código · 3. Ingresa</text>
    <rect x="120" y="1060" width="660" height="54" rx="27" fill="#eff6ff" />
    <text x="450" y="1095" fill="#1d4ed8" font-family="Arial, sans-serif" font-size="18" font-weight="700" text-anchor="middle" textLength="600" lengthAdjust="spacingAndGlyphs">{{ $portalUrl }}</text>
</svg>

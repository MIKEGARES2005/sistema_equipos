@php
    $portalUrl = $portalUrl ?? null;
    $qrCodeUrl = $qrCodeUrl ?? null;
    $embedded = request()->boolean('embed');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso estudiantil · {{ config('app.name', 'Sistema de Gestión') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap"
        rel="stylesheet"
    />

    @vite(['resources/css/app.css'])

    <style>
        @media print {
            @page {
                size: Letter portrait;
                margin: 0;
            }

            body,
            .qr-page-shell {
                background: #fff !important;
                margin: 0 !important;
                overflow: hidden !important;
            }

            .qr-page-shell {
                box-sizing: border-box !important;
                width: 8.5in !important;
                height: 11in !important;
                min-height: 11in !important;
                padding: 0.35in !important;
            }

            .qr-page-shell > main {
                display: block !important;
                min-height: 0 !important;
                padding: 0 !important;
            }

            .qr-page-shell > main > div {
                max-width: none !important;
                width: 100% !important;
            }

            .qr-card {
                box-sizing: border-box !important;
                height: 10.3in !important;
                min-height: 0 !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.3in !important;
                box-shadow: none !important;
            }

            .qr-header {
                background: #0f172a !important;
                padding: 0.38in 0.55in !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .qr-header h1 {
                font-size: 25pt !important;
            }

            .qr-print-details .step-number {
                background: #1d4ed8 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .qr-print-content {
                padding: 0.32in 0.55in !important;
            }

            .qr-image-frame {
                max-width: 4.55in !important;
                padding: 0.18in !important;
            }

            .qr-print-details {
                display: block !important;
                margin-top: 0.22in !important;
                padding: 0.16in !important;
                border-color: #bfdbfe !important;
                background: #eff6ff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body
    class="min-h-screen bg-slate-950 antialiased"
    style="font-family: 'Manrope', sans-serif;"
>
    <div class="qr-page-shell relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-900 via-blue-950 to-indigo-950">
        {{-- Fondo decorativo --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="login-orb absolute -left-32 -top-32 h-96 w-96 rounded-full bg-cyan-500/15 blur-3xl"></div>
            <div class="login-orb login-orb-delayed absolute -bottom-40 -right-24 h-[30rem] w-[30rem] rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="login-grid absolute inset-0 opacity-50"></div>
        </div>

        <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
            <div class="w-full max-w-xl">
                <div class="qr-card relative overflow-hidden rounded-[2rem] border border-white/10 bg-white shadow-2xl shadow-slate-950/50">
                    {{-- Encabezado --}}
                    <div class="qr-header relative overflow-hidden bg-gradient-to-r from-slate-950 via-blue-950 to-indigo-950 px-6 py-6 text-white sm:px-10">
                        <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-cyan-400/20 blur-3xl" aria-hidden="true"></div>
                        <div class="relative">
                            <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-cyan-300">
                                INSTITUTO TECNOLÓGICO &#39;ITIBB&#39;
                            </p>
                            <h1 class="mt-2 text-2xl font-black tracking-tight">
                                Acceso Estudiantil
                            </h1>
                            <p class="mt-1 text-sm text-slate-300">
                                Código QR para ingresar al portal del estudiante.
                            </p>
                        </div>
                    </div>

                    <div class="qr-print-content px-6 py-8 sm:px-10">
                        @if ($portalUrl && $qrCodeUrl)
                            <div class="qr-image-frame mx-auto w-full max-w-[360px] rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                                <img
                                    src="{{ $qrCodeUrl }}"
                                    alt="Código QR de acceso al portal estudiantil"
                                    class="h-auto w-full rounded-xl"
                                >
                            </div>

                            <p class="mt-5 text-center text-sm font-semibold text-slate-700">
                                Apunta con la cámara de tu tablet o celular.
                            </p>
                            <p class="mt-2 break-all text-center text-xs text-slate-400">
                                {{ $portalUrl }}
                            </p>

                            <div class="qr-print-details mt-8 hidden rounded-2xl border border-blue-100 bg-blue-50 p-5">
                                <p class="text-center text-sm font-bold text-blue-950">Ingresa al portal en tres pasos</p>
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    <div class="rounded-xl bg-white/80 px-2 py-2.5 text-center ring-1 ring-blue-100">
                                        <span class="step-number mx-auto flex h-6 w-6 items-center justify-center rounded-full bg-blue-700 text-[10px] font-black text-white">01</span>
                                        <p class="mt-1.5 text-[10px] font-bold text-blue-950">Abre la cámara</p>
                                    </div>
                                    <div class="rounded-xl bg-white/80 px-2 py-2.5 text-center ring-1 ring-blue-100">
                                        <span class="step-number mx-auto flex h-6 w-6 items-center justify-center rounded-full bg-blue-700 text-[10px] font-black text-white">02</span>
                                        <p class="mt-1.5 text-[10px] font-bold text-blue-950">Escanea el código</p>
                                    </div>
                                    <div class="rounded-xl bg-white/80 px-2 py-2.5 text-center ring-1 ring-blue-100">
                                        <span class="step-number mx-auto flex h-6 w-6 items-center justify-center rounded-full bg-blue-700 text-[10px] font-black text-white">03</span>
                                        <p class="mt-1.5 text-[10px] font-bold text-blue-950">Toca el aviso</p>
                                    </div>
                                </div>
                                <div class="mt-3 rounded-xl border border-blue-100 bg-white/70 px-3 py-2 text-center">
                                    <span class="text-[9px] font-bold uppercase tracking-[0.18em] text-blue-500">Portal oficial</span>
                                    <p class="mt-1 break-all text-[10px] font-semibold text-slate-700">{{ $portalUrl }}</p>
                                </div>
                            </div>

                            <div class="no-print mt-6 flex flex-col gap-3 sm:flex-row">
                                <a
                                    href="{{ route('estudiante.qr.download') }}"
                                    download="qr-acceso-estudiantil.png"
                                    onclick="downloadStyledQr(event, this)"
                                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    Descargar imagen QR
                                </a>
                                <button
                                    type="button"
                                    onclick="window.print()"
                                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/60 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Z" />
                                    </svg>
                                    Imprimir QR
                                </button>
                            </div>
                        @else
                            <div class="mx-auto flex aspect-square w-full max-w-[360px] flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                                <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-slate-300 shadow-sm ring-1 ring-slate-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3.5" y="3.5" width="6" height="6" rx="1" />
                                        <rect x="14.5" y="3.5" width="6" height="6" rx="1" />
                                        <rect x="3.5" y="14.5" width="6" height="6" rx="1" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 14.5h2.5v2.5h-2.5zM18.5 14.5h2v2h-2zM14.5 18.5h2v2h-2zM18.5 18.5h2v2h-2z" />
                                    </svg>
                                </span>
                                <span class="mt-4 inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-amber-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Túnel pendiente
                                </span>
                                <p class="mt-3 text-sm font-bold text-slate-700">
                                    QR pendiente de configuración
                                </p>
                                <p class="mt-2 text-xs leading-6 text-slate-500">Conecta el tunel externo y define <code class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-slate-700">ESTUDIANTE_PORTAL_URL</code> con la URL publica para generar el codigo.</p>
                            </div>
                        @endif

                        @unless ($embedded)
                            <div class="no-print mt-8 border-t border-slate-100 pt-5 text-center">
                                <a
                                    href="{{ route('login') }}"
                                    onclick="event.preventDefault(); if (window.history.length > 1) { window.history.back(); } else { window.location.href = this.href; }"
                                    class="text-xs font-semibold text-slate-500 transition hover:text-blue-700 hover:underline"
                                >
                                    Volver al acceso administrativo / docente
                                </a>
                            </div>
                        @endunless
                    </div>
                </div>

                <p class="no-print mt-5 text-center text-xs text-white/60">
                    Monitoreo, control y diagnóstico en tiempo real.
                </p>
            </div>
        </main>
    </div>

    <script>
        async function downloadStyledQr(event, link) {
            event.preventDefault();

            const originalLabel = link.innerHTML;
            link.setAttribute('aria-busy', 'true');
            link.classList.add('pointer-events-none', 'opacity-70');
            link.innerHTML = 'Preparando imagen...';

            try {
                const response = await fetch(link.href, {
                    headers: { 'Accept': 'image/svg+xml' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('No se pudo generar la imagen.');
                }

                const svgBlob = await response.blob();
                const imageUrl = URL.createObjectURL(svgBlob);
                const image = new Image();

                await new Promise((resolve, reject) => {
                    image.onload = resolve;
                    image.onerror = reject;
                    image.src = imageUrl;
                });

                const canvas = document.createElement('canvas');
                canvas.width = 1800;
                canvas.height = 2400;

                const context = canvas.getContext('2d');
                context.fillStyle = '#f8fafc';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(imageUrl);

                const pngBlob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
                const downloadUrl = URL.createObjectURL(pngBlob);
                const downloadLink = document.createElement('a');
                downloadLink.href = downloadUrl;
                downloadLink.download = 'qr-acceso-estudiantil.png';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                downloadLink.remove();
                URL.revokeObjectURL(downloadUrl);
            } catch (error) {
                window.location.href = link.href;
            } finally {
                link.innerHTML = originalLabel;
                link.classList.remove('pointer-events-none', 'opacity-70');
                link.removeAttribute('aria-busy');
            }
        }
    </script>
</body>
</html>

import Alpine from 'alpinejs';

const toastTimers = new Map();

Alpine.store('toasts', {
    items: [],
    nextId: 1,

    push(payload = {}) {
        const message = String(payload.message ?? '').trim();

        if (message === '') {
            return;
        }

        const type = payload.type ?? 'info';
        const defaultDuration = ['error', 'warning'].includes(type) ? 9000 : 6000;

        // Los mensajes largos (por ejemplo, claves temporales) permanecen
        // visibles hasta que el usuario los cierre.
        const duration = payload.duration ?? (message.length > 160 ? 0 : defaultDuration);
        const id = this.nextId++;

        this.items.push({ id, type, message, duration });

        if (duration > 0) {
            toastTimers.set(id, setTimeout(() => this.dismiss(id), duration));
        }
    },

    pushMany(messages = []) {
        messages.forEach((message) => this.push(message));
    },

    dismiss(id) {
        const timer = toastTimers.get(id);

        if (timer) {
            clearTimeout(timer);
            toastTimers.delete(id);
        }

        this.items = this.items.filter((toast) => toast.id !== id);
    },
});

window.Alpine = Alpine;

Alpine.start();

/**
 * Abre el portal estudiantil reutilizando siempre la misma ventana.
 */
window.abrirQrEstudiante = function (url) {
    const ventana = window.open(url, 'estudianteQrWindow');

    if (ventana) {
        ventana.focus();
        return;
    }

    window.location.href = url;
};

/**
 * Cierra la ventana del QR cuando fue abierta desde el login.
 * Si se accedió directamente, vuelve al login en la misma pestaña.
 */
window.volverAlLoginDesdeQr = function (loginUrl) {
    const abiertaDesdeLogin = (window.opener && !window.opener.closed)
        || window.name === 'estudianteQrWindow';

    if (!abiertaDesdeLogin) {
        window.location.href = loginUrl;
        return;
    }

    window.close();

    window.setTimeout(function () {
        if (!window.closed) {
            window.location.href = loginUrl;
        }
    }, 150);
};

const QR_FONT_STACK = 'Manrope, "Segoe UI", system-ui, -apple-system, sans-serif';

function qrRoundRect(ctx, x, y, width, height, radius) {
    const r = Math.min(radius, width / 2, height / 2);

    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + width, y, x + width, y + height, r);
    ctx.arcTo(x + width, y + height, x, y + height, r);
    ctx.arcTo(x, y + height, x, y, r);
    ctx.arcTo(x, y, x + width, y, r);
    ctx.closePath();
}

function qrCargarImagen(src) {
    return new Promise((resolve, reject) => {
        const imagen = new Image();
        imagen.onload = () => resolve(imagen);
        imagen.onerror = () => reject(new Error('No se pudo cargar el código QR.'));
        imagen.src = src;
    });
}

function qrAjustarTexto(ctx, texto, anchoMaximo, tamanoMaximo, tamanoMinimo, peso = '700') {
    let tamano = tamanoMaximo;

    while (tamano > tamanoMinimo) {
        ctx.font = `${peso} ${tamano}px ${QR_FONT_STACK}`;

        if (ctx.measureText(texto).width <= anchoMaximo) {
            break;
        }

        tamano -= 1;
    }

    return tamano;
}

/**
 * Genera una tarjeta PNG estética (no tamaño carta) con el QR y los datos del portal.
 */
window.descargarQrEstudiante = async function ({
    qrSrc,
    portalUrl = '',
    filename = 'qr-acceso-estudiantil.png',
    button = null,
} = {}) {
    if (!qrSrc) {
        window.alert('El código QR todavía no está disponible.');
        return;
    }

    const etiquetaOriginal = button ? button.innerHTML : null;
    const textoOriginal = button ? button.textContent : null;

    if (button) {
        button.disabled = true;
        button.textContent = 'Generando imagen...';
    }

    try {
        if (document.fonts?.ready) {
            await document.fonts.ready;
        }

        const imagenQr = await qrCargarImagen(qrSrc);
        const canvas = document.createElement('canvas');
        canvas.width = 1000;
        canvas.height = 1250;

        const ctx = canvas.getContext('2d');

        const fondo = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
        fondo.addColorStop(0, '#050b1f');
        fondo.addColorStop(0.45, '#0b2a5b');
        fondo.addColorStop(1, '#211c4f');
        ctx.fillStyle = fondo;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        const brilloSuperior = ctx.createRadialGradient(180, 40, 0, 180, 40, 470);
        brilloSuperior.addColorStop(0, 'rgba(34, 211, 238, 0.28)');
        brilloSuperior.addColorStop(1, 'rgba(34, 211, 238, 0)');
        ctx.fillStyle = brilloSuperior;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        const brilloInferior = ctx.createRadialGradient(900, 1180, 0, 900, 1180, 520);
        brilloInferior.addColorStop(0, 'rgba(99, 102, 241, 0.32)');
        brilloInferior.addColorStop(1, 'rgba(99, 102, 241, 0)');
        ctx.fillStyle = brilloInferior;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.strokeStyle = 'rgba(148, 163, 184, 0.06)';
        ctx.lineWidth = 1;

        for (let x = 0; x <= canvas.width; x += 50) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, canvas.height);
            ctx.stroke();
        }

        for (let y = 0; y <= canvas.height; y += 50) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(canvas.width, y);
            ctx.stroke();
        }

        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        if ('letterSpacing' in ctx) {
            ctx.letterSpacing = '5px';
        }

        ctx.fillStyle = '#67e8f9';
        ctx.font = `800 22px ${QR_FONT_STACK}`;
        ctx.fillText('INSTITUTO TECNOLÓGICO «ITIBB»', 500, 96);

        if ('letterSpacing' in ctx) {
            ctx.letterSpacing = '0px';
        }

        ctx.fillStyle = '#ffffff';
        ctx.font = `900 66px ${QR_FONT_STACK}`;
        ctx.fillText('Acceso Estudiantil', 500, 176);

        ctx.fillStyle = '#cbd5e1';
        ctx.font = `500 26px ${QR_FONT_STACK}`;
        ctx.fillText('Escanea este código para ingresar al Portal del Estudiante', 500, 232);

        const tarjeta = { x: 100, y: 292, width: 800, height: 790, radius: 52 };

        ctx.save();
        ctx.shadowColor = 'rgba(2, 6, 23, 0.55)';
        ctx.shadowBlur = 70;
        ctx.shadowOffsetY = 28;
        ctx.fillStyle = '#ffffff';
        qrRoundRect(ctx, tarjeta.x, tarjeta.y, tarjeta.width, tarjeta.height, tarjeta.radius);
        ctx.fill();
        ctx.restore();

        const borde = ctx.createLinearGradient(
            tarjeta.x,
            tarjeta.y,
            tarjeta.x + tarjeta.width,
            tarjeta.y + tarjeta.height,
        );
        borde.addColorStop(0, 'rgba(34, 211, 238, 0.95)');
        borde.addColorStop(0.5, 'rgba(255, 255, 255, 0.55)');
        borde.addColorStop(1, 'rgba(129, 140, 248, 0.95)');
        ctx.strokeStyle = borde;
        ctx.lineWidth = 3;
        qrRoundRect(ctx, tarjeta.x, tarjeta.y, tarjeta.width, tarjeta.height, tarjeta.radius);
        ctx.stroke();

        const tamanoQr = 610;
        const qrX = (canvas.width - tamanoQr) / 2;
        const qrY = tarjeta.y + (tarjeta.height - tamanoQr) / 2;
        ctx.drawImage(imagenQr, qrX, qrY, tamanoQr, tamanoQr);

        ctx.fillStyle = '#e2e8f0';
        ctx.font = `600 27px ${QR_FONT_STACK}`;
        ctx.fillText('Apunta con la cámara de tu celular o tablet', 500, 1128);

        const pildora = { x: 70, y: 1158, width: 860, height: 58, radius: 29 };
        ctx.fillStyle = 'rgba(255, 255, 255, 0.10)';
        qrRoundRect(ctx, pildora.x, pildora.y, pildora.width, pildora.height, pildora.radius);
        ctx.fill();

        ctx.strokeStyle = 'rgba(255, 255, 255, 0.20)';
        ctx.lineWidth = 1.5;
        qrRoundRect(ctx, pildora.x, pildora.y, pildora.width, pildora.height, pildora.radius);
        ctx.stroke();

        const textoUrl = portalUrl || 'Portal del Estudiante';
        const tamanoUrl = qrAjustarTexto(ctx, textoUrl, 810, 22, 12, '600');
        ctx.fillStyle = '#e0f2fe';
        ctx.font = `600 ${tamanoUrl}px ${QR_FONT_STACK}`;
        ctx.fillText(textoUrl, 500, 1188);

        ctx.fillStyle = '#94a3b8';
        ctx.font = `500 20px ${QR_FONT_STACK}`;
        ctx.fillText('Gestión de equipos de cómputo · Informática Industrial', 500, 1240);

        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));

        if (!blob) {
            throw new Error('No se pudo generar el archivo PNG.');
        }

        const enlace = document.createElement('a');
        const objectUrl = URL.createObjectURL(blob);
        enlace.href = objectUrl;
        enlace.download = filename;
        document.body.appendChild(enlace);
        enlace.click();
        enlace.remove();
        window.setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
    } catch (error) {
        console.error(error);
        window.alert('No se pudo generar la imagen del QR. Intenta nuevamente.');
    } finally {
        if (button) {
            button.disabled = false;
            button.innerHTML = etiquetaOriginal ?? textoOriginal ?? 'Descargar imagen';
        }
    }
};

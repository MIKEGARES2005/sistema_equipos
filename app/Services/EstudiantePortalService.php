<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class EstudiantePortalService
{
    public function __construct(private CloudflareTunnelService $cloudflareTunnel) {}

    public function loginUrl(): ?string
    {
        if (Cache::has(CloudflareTunnelService::UNAVAILABLE_CACHE_KEY)) {
            return null;
        }

        $url = Cache::get(
            CloudflareTunnelService::PUBLIC_URL_CACHE_KEY,
            config('services.estudiante_portal.url'),
        );

        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $url = trim($url);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if ($path === null || $path === '' || $path === '/') {
            $url = rtrim($url, '/').'/estudiante/login';
        }

        return $url;
    }

    public function qrCodeUrl(int $size = 300): ?string
    {
        $loginUrl = $this->loginUrl();

        if (! $this->isPublicUrl($loginUrl)) {
            return null;
        }

        $size = max(100, min(1000, $size));

        return 'https://api.qrserver.com/v1/create-qr-code/?size='.$size.'x'.$size.'&data='.urlencode($loginUrl);
    }

    /**
     * @return array{available: bool, starting: bool, portalUrl: ?string, qrCodeUrl: ?string, message: string}
     */
    public function connectionStatus(): array
    {
        $this->cloudflareTunnel->syncPublicUrl();

        if (Cache::has(CloudflareTunnelService::START_LOCK_KEY)) {
            return [
                'available' => false,
                'starting' => true,
                'portalUrl' => null,
                'qrCodeUrl' => null,
                'message' => 'Iniciando Cloudflare Tunnel...',
            ];
        }

        $portalUrl = $this->loginUrl();

        if (! $this->isPublicUrl($portalUrl)) {
            return [
                'available' => false,
                'starting' => false,
                'portalUrl' => null,
                'qrCodeUrl' => null,
                'message' => $this->cloudflareTunnel->lastError()
                    ?? 'No hay una conexion activa con Cloudflare Tunnel.',
            ];
        }

        // Solo verificamos el proceso local cuando la URL vino del log del
        // tunel; una URL fija configurada a mano puede vivir en otro equipo.
        if (Cache::has(CloudflareTunnelService::PUBLIC_URL_CACHE_KEY) && ! $this->cloudflareTunnel->isRunning()) {
            $this->cloudflareTunnel->markUnavailable($portalUrl);

            return [
                'available' => false,
                'starting' => false,
                'portalUrl' => null,
                'qrCodeUrl' => null,
                'message' => 'No hay conexion con Cloudflare Tunnel.',
            ];
        }

        return [
            'available' => true,
            'starting' => false,
            'portalUrl' => $portalUrl,
            'qrCodeUrl' => $this->qrCodeUrl(320),
            'message' => 'Portal Publico conectado correctamente.',
        ];
    }

    private function isPublicUrl(?string $url): bool
    {
        if (! is_string($url)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return parse_url($url, PHP_URL_SCHEME) === 'https'
            && is_string($host)
            && ! in_array(strtolower($host), ['localhost', '127.0.0.1', '::1'], true);
    }
}

<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class CloudflareTunnelService
{
    public const PUBLIC_URL_CACHE_KEY = 'estudiante_portal.public_url';

    public const START_LOCK_KEY = 'estudiante_portal.tunnel_starting';

    public const UNAVAILABLE_CACHE_KEY = 'estudiante_portal.tunnel_unavailable';

    public const FAILED_URL_CACHE_KEY = 'estudiante_portal.failed_url';

    private const UNAVAILABLE_MINUTES = 2;

    /**
     * Start cloudflared without blocking the HTTP request.
     *
     * @return array{available: bool, starting: bool, portalUrl: ?string, message: string}
     */
    public function reconnect(): array
    {
        $binary = config('services.cloudflare.binary');
        $origin = config('services.cloudflare.origin');

        if (! is_string($binary) || ! is_file($binary)) {
            return $this->failure('No se encontro cloudflared.exe en la ruta configurada.');
        }

        if (! is_string($origin) || ! filter_var($origin, FILTER_VALIDATE_URL)) {
            return $this->failure('La direccion local del sistema no esta configurada correctamente.');
        }

        if (! Cache::add(self::START_LOCK_KEY, true, now()->addSeconds(35))) {
            return [
                'available' => false,
                'starting' => true,
                'portalUrl' => null,
                'message' => 'Cloudflare Tunnel ya se esta iniciando. Espera unos segundos.',
            ];
        }

        Cache::forget(self::PUBLIC_URL_CACHE_KEY);
        Cache::forget(self::FAILED_URL_CACHE_KEY);
        Cache::forget(self::UNAVAILABLE_CACHE_KEY);

        try {
            $logPath = $this->logPath();
            $attempts = 0;

            do {
                file_put_contents($logPath, '');
                $this->launchTunnel($binary, $origin, $logPath);
                $attempts++;

                // cloudflared falla rapido cuando el DNS no resuelve; le damos
                // un par de oportunidades antes de rendirnos.
                sleep(2);
            } while ($attempts < 3 && str_contains($this->logOutput(), 'failed to request quick Tunnel'));

            return [
                'available' => false,
                'starting' => true,
                'portalUrl' => null,
                'message' => 'Iniciando Cloudflare Tunnel...',
            ];
        } catch (\Throwable) {
            Cache::forget(self::START_LOCK_KEY);

            return $this->failure('No se pudo iniciar Cloudflare Tunnel desde el sistema.');
        }
    }

    /**
     * Lee la URL publica que cloudflared escribe en su log.
     */
    public function syncPublicUrl(): ?string
    {
        $output = $this->logOutput();

        if (preg_match_all('~https://(?!api\.)([a-z0-9-]+)\.trycloudflare\.com~i', $output, $matches) < 1) {
            return null;
        }

        $urls = $matches[0];
        $publicUrl = rtrim(end($urls), '/');

        // Si la URL ya fallo hace poco, no la volvemos a usar hasta que expire
        // la ventana de indisponibilidad (salvo que el log traiga otra nueva).
        if (Cache::has(self::UNAVAILABLE_CACHE_KEY) && Cache::get(self::FAILED_URL_CACHE_KEY) === $publicUrl) {
            return null;
        }

        Cache::put(self::PUBLIC_URL_CACHE_KEY, $publicUrl, now()->addHours(12));
        Cache::forget(self::UNAVAILABLE_CACHE_KEY);
        Cache::forget(self::FAILED_URL_CACHE_KEY);
        Cache::forget(self::START_LOCK_KEY);

        return $publicUrl;
    }

    /**
     * Comprueba localmente si cloudflared sigue conectado usando su endpoint
     * de readiness (no hace peticiones al propio portal).
     */
    public function isRunning(): bool
    {
        $output = $this->logOutput();

        if (preg_match_all('~Starting metrics server on 127\.0\.0\.1:(\d+)/metrics~', $output, $matches) < 1) {
            return true;
        }

        $ports = $matches[1];
        $port = end($ports);

        try {
            $response = Http::connectTimeout(1)
                ->timeout(2)
                ->get("http://127.0.0.1:{$port}/ready");
        } catch (ConnectionException) {
            return false;
        }

        return $response->successful();
    }

    public function lastError(): ?string
    {
        if (str_contains($this->logOutput(), 'failed to request quick Tunnel')) {
            return 'Cloudflare no pudo resolver el DNS. Revisa la conexion a Internet e intenta de nuevo.';
        }

        return null;
    }

    public function isUnavailable(): bool
    {
        return Cache::has(self::UNAVAILABLE_CACHE_KEY);
    }

    public function markUnavailable(?string $url = null): void
    {
        if (is_string($url) && $url !== '') {
            Cache::put(self::FAILED_URL_CACHE_KEY, $url, now()->addHours(12));
        }

        Cache::forget(self::PUBLIC_URL_CACHE_KEY);
        Cache::put(self::UNAVAILABLE_CACHE_KEY, true, now()->addMinutes(self::UNAVAILABLE_MINUTES));
    }

    private function launchTunnel(string $binary, string $origin, string $logPath): void
    {
        $command = sprintf(
            'cmd /D /S /C start "" /B %s tunnel --url %s --no-autoupdate --edge-ip-version 4 1> %s 2>&1',
            escapeshellarg($binary),
            escapeshellarg($origin),
            escapeshellarg($logPath),
        );

        // En el SAPI web Symfony Process recorta el entorno a las claves de
        // $_SERVER y deja fuera PATH/SystemRoot; sin ellas cloudflared no
        // puede inicializar Winsock y falla la resolucion DNS.
        $launcher = Process::fromShellCommandline($command, null, getenv());
        $launcher->setTimeout(5);
        $launcher->run();
    }

    private function logOutput(): string
    {
        $path = $this->logPath();
        $output = is_file($path) ? file_get_contents($path) : '';

        return is_string($output) ? $output : '';
    }

    private function logPath(): string
    {
        $path = config('services.cloudflare.log_path');

        if (! is_string($path) || $path === '') {
            return storage_path('app/cloudflared-tunnel.log');
        }

        $isAbsolute = str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || preg_match('~^[A-Za-z]:[\\\\/]~', $path) === 1;

        return $isAbsolute ? $path : base_path($path);
    }

    /**
     * @return array{available: bool, starting: bool, portalUrl: ?string, message: string}
     */
    private function failure(string $message): array
    {
        return [
            'available' => false,
            'starting' => false,
            'portalUrl' => null,
            'message' => $message,
        ];
    }
}

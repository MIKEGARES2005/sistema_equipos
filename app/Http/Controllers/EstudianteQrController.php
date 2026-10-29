<?php

namespace App\Http\Controllers;

use App\Services\CloudflareTunnelService;
use App\Services\EstudiantePortalService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class EstudianteQrController extends Controller
{
    public function show(EstudiantePortalService $estudiantePortal): View
    {
        $connectionStatus = $estudiantePortal->connectionStatus();

        return view('auth.estudiante-qr', [
            'portalUrl' => $connectionStatus['available'] ? $connectionStatus['portalUrl'] : null,
            'qrCodeUrl' => $connectionStatus['available'] ? $connectionStatus['qrCodeUrl'] : null,
        ]);
    }

    public function status(EstudiantePortalService $estudiantePortal): JsonResponse
    {
        return response()->json($estudiantePortal->connectionStatus());
    }

    public function reconnect(Request $request, CloudflareTunnelService $cloudflareTunnel): JsonResponse
    {
        abort_unless($this->isLocalNetworkRequest($request), 403);

        return response()->json($cloudflareTunnel->reconnect());
    }

    public function image(EstudiantePortalService $estudiantePortal): Response
    {
        $png = $this->fetchQrPng($estudiantePortal);

        abort_if($png === null, 404);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    public function download(EstudiantePortalService $estudiantePortal): Response
    {
        $qrCodeUrl = $estudiantePortal->qrCodeUrl(800);

        abort_if($qrCodeUrl === null, 404);

        try {
            $response = $this->qrApiRequest($qrCodeUrl);
        } catch (ConnectionException) {
            abort(502, 'No se pudo conectar con el generador del código QR.');
        }

        abort_unless($response->successful(), 502, 'No se pudo generar el código QR.');

        $svg = view('auth.qr-download', [
            'portalUrl' => $estudiantePortal->loginUrl(),
            'qrDataUri' => 'data:image/png;base64,'.base64_encode($response->body()),
        ])->render();

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="qr-acceso-estudiantil.svg"',
            'Cache-Control' => 'no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Permite reconectar desde el propio equipo (loopback) o desde la red
     * local, pero no desde Internet a traves del tunel.
     */
    private function isLocalNetworkRequest(Request $request): bool
    {
        $ip = $request->ip();

        if (! is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    private function fetchQrPng(EstudiantePortalService $estudiantePortal): ?string
    {
        $qrCodeUrl = $estudiantePortal->qrCodeUrl(800);

        if ($qrCodeUrl === null) {
            return null;
        }

        try {
            $response = $this->qrApiRequest($qrCodeUrl);
        } catch (ConnectionException) {
            return null;
        }

        return $response->successful() ? $response->body() : null;
    }

    private function qrApiRequest(string $qrCodeUrl): HttpResponse
    {
        return Http::withOptions([
            'verify' => config('services.http.ca_bundle') ?: true,
        ])->timeout(10)->get($qrCodeUrl);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SancionVerificationClient
{
    private readonly string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.sanciones.base_url', 'http://localhost:8087'), '/');
    }

    public function verificarSancionActiva(?int $usuarioId, ?string $tipoUsuario): ?array
    {
        if ($usuarioId === null || $tipoUsuario === null || trim($tipoUsuario) === '') {
            return null;
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/sanciones/verificacion", [
                'usuarioId' => $usuarioId,
                'tipoUsuario' => $tipoUsuario,
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $ex) {
            Log::warning("No se pudo verificar sanciones para el usuario {$usuarioId}: {$ex->getMessage()}");

            return null;
        }
    }
}
